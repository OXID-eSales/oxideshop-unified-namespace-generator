<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use FilesystemIterator;
use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\Facts\Facts;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Filesystem\Path;

class Generator
{
    public function __construct(
        private readonly Facts $facts,
        private readonly UnifiedNameSpaceClassMapProvider $unifiedNameSpaceClassMapProvider,
        //phpcs:disable
        private readonly string $outputDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR,
        private readonly string $templateDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
        private string $shopEdition = '',
        protected readonly string $communityEdition = EditionSelector::COMMUNITY,
        protected readonly string $professionalEdition = EditionSelector::PROFESSIONAL,
        protected readonly string $enterpriseEdition = EditionSelector::ENTERPRISE,
        private readonly Filesystem $fileSystem = new Filesystem(),
    ) {
        $this->validateOutputDirectoryPermissions();

        $this->shopEdition = $facts->getEdition();
        $this->validateShopEdition($this->shopEdition);
    }

    public function cleanupOutputDirectory(): void
    {
        $directoryIterator = new \RecursiveDirectoryIterator(
            $this->outputDirectory,
            FilesystemIterator::SKIP_DOTS
        );

        foreach ($directoryIterator as $current) {
            if (!str_contains($current->getFilename(), '.gitkeep')) {
                $this->fileSystem->remove($current->getPathname());
            }
        }
    }

    public function generate(): void
    {
        $classMap = $this->unifiedNameSpaceClassMapProvider->getClassMap();

        $this->generateClassFiles($classMap);
    }

    protected function generateClassFiles(array $classMap): void
    {
        $backwardsCompatibilityMap = $this->getBackwardsCompatibilityMap();

        $unifiedNamespaceArray = $this->getUnifiedNamespaceArray($classMap);
        $this->validateUnifiedNamespaceArray($unifiedNamespaceArray);

        foreach ($unifiedNamespaceArray as $unifiedSubNamespace => $editionClassDescriptions) {
            $this->buildSubNamespace($unifiedSubNamespace, $editionClassDescriptions, $backwardsCompatibilityMap);
        }
    }

    protected function getBackwardsCompatibilityMap(): array
    {
        $backwardsCompatibilityClassMapProvider = new BackwardsCompatibilityClassMapProvider($this->facts);
        return $backwardsCompatibilityClassMapProvider->getClassMap();
    }

    protected function getUnifiedNamespaceArray(array $classMap): array
    {
        $unifiedNameSpace = [];

        foreach ($classMap as $fullyQualifiedUnifiedClass => $editionClassDescription) {
            $parts = explode('\\', $fullyQualifiedUnifiedClass);
            $shortUnifiedClassName = array_pop($parts);
            $this->validateShortUnifiedClassName($shortUnifiedClassName, $fullyQualifiedUnifiedClass);

            $unifiedSubNamespace = implode('\\', $parts);
            $this->validateUnifiedNamespace($unifiedSubNamespace, $fullyQualifiedUnifiedClass);

            $this->validateEditionClassDescription($editionClassDescription);
            $unifiedNameSpace[$unifiedSubNamespace][] = [
                // Interfaces are abstract for Reflection too, here we want just abstract classes
                'isAbstract'            => $editionClassDescription['isAbstract'],
                'isInterface'           => $editionClassDescription['isInterface'],
                'isDeprecated'          => $editionClassDescription['isDeprecated'],
                'shortUnifiedClassName' => $shortUnifiedClassName,
                'editionClassName'      => $editionClassDescription['editionClassName'],
            ];
        }

        return $unifiedNameSpace;
    }

    protected function buildSubNamespace(
        string $unifiedSubNamespace,
        array $editionClassDescriptions,
        array $backwardsCompatibilityMap
    ): void {
        $subNamespacePath = $this->createUnifiedNamespaceSubDirectory($unifiedSubNamespace);

        foreach ($editionClassDescriptions as $editionClassDescription) {
            $shortUnifiedClassName = $editionClassDescription['shortUnifiedClassName'];
            $filePath = Path::join($subNamespacePath, $shortUnifiedClassName . '.php');
            $fullyQualifiedUnifiedClass = '\\' . trim($unifiedSubNamespace . '\\' . $shortUnifiedClassName, '\\');

            $backwardsCompatibleClass = $this->getBackwardsCompatibleClass(
                $fullyQualifiedUnifiedClass,
                $backwardsCompatibilityMap
            );

            $content = $this->renderContent(
                $unifiedSubNamespace,
                $editionClassDescription,
                $fullyQualifiedUnifiedClass,
                $backwardsCompatibleClass
            );

            $this->writeFile($filePath, $content);
        }
    }

    private function getBackwardsCompatibleClass(
        string $fullyQualifiedUnifiedClass,
        array $backwardsCompatibilityMap
    ): ?string
    {
        $backwardsCompatibilityMapIndex = trim($fullyQualifiedUnifiedClass, '\\');

        return $backwardsCompatibilityMap[$backwardsCompatibilityMapIndex] ?? null;
    }

    protected function renderContent(
        string $unifiedSubNamespace,
        array $editionClassDescription,
        string $fullyQualifiedUnifiedClass,
        ?string $backwardsCompatibleClass
    ): string {
        $templating = $this->getTemplatingEngine();

        return $templating->render('class_file_template.php', [
            'shopEdition' => $this->shopEdition,
            'class' => $editionClassDescription,
            'namespace' => $unifiedSubNamespace,
            'fullyQualifiedUnifiedClass' => $fullyQualifiedUnifiedClass,
            'backwardsCompatibleClass' => $backwardsCompatibleClass,
        ]);
    }

    protected function writeFile(string $filePath, string $content): void
    {
        $this->validateOutputDirectoryPermissions();

        $currentDirectory = dirname($filePath);
        if (!is_writable($currentDirectory)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\PermissionException(
                sprintf(
                    'Could not create file %s. The directory %s is not writable for user "%s".' .
                    'Please fix the permissions on this directory and run this script again.',
                    $filePath,
                    $currentDirectory,
                    get_current_user()
                ),
                ErrorEnum::CODE_FILE_CREATION_ERROR->value
            );
        }

        $fileHandle = fopen($filePath, 'wb');
        if (!$fileHandle) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\FileSystemCompatibilityException(
                sprintf(
                    'Could not open file handle for %s. There might be a problem with your file system.' .
                    'Try to solve this problem and run this script again.',
                    $filePath
                ),
                ErrorEnum::CODE_FILE_CREATION_ERROR->value
            );
        }

        $result = fwrite($fileHandle, $content);
        fclose($fileHandle);
        if ($result === false) {
            throw new \Exception(
                sprintf('Could not create file %s', $filePath)
            );
        }
        if ($result === 0) {
            throw new \Exception(
                sprintf('Created empty file %s', $filePath),
                ErrorEnum::CODE_FILE_CREATION_ERROR->value
            );
        }
    }

    protected function validateUnifiedNamespace(string $unifiedSubNamespace, string $fullyQualifiedUnifiedClass): void
    {
        if (!$unifiedSubNamespace) {
            throw new \Exception(
                'Could not extract unified sub namespace from string ' . $fullyQualifiedUnifiedClass,
                ErrorEnum::CODE_INVALID_UNIFIED_NAMESPACE->value
            );
        }
    }

    protected function validateEditionClassDescription(array $editionClassDescription): void
    {
        $expectedKeys = ['isAbstract', 'isInterface', 'editionClassName', 'isDeprecated'];
        $message = 'Edition class description has a wrong layout. ' .
                   'It must be a non-empty array with the following keys ' . implode(',', $expectedKeys) . ' ';

        if (!is_array($editionClassDescription) || empty($editionClassDescription)) {
            throw new \Exception($message, ErrorEnum::CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP->value);
        }

        $actualKeys = array_keys($editionClassDescription);
        sort($expectedKeys);
        sort($actualKeys);
        if ($expectedKeys != $actualKeys) {
            $message .= ' Actual edition class description is ' . var_export($editionClassDescription, true);
            throw new \Exception($message, ErrorEnum::CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP->value);
        }
    }

    protected function validateShortUnifiedClassName(
        string $shortUnifiedClassName,
        string $fullyQualifiedUnifiedClass
    ): void
    {
        if (!$shortUnifiedClassName) {
            throw new \Exception(
                'Could not extract short unified a class name from string ' . $fullyQualifiedUnifiedClass,
                ErrorEnum::CODE_INVALID_UNIFIED_CLASS_NAME->value
            );
        }
    }

    protected function validateShopEdition(string $shopEdition): void
    {
        $expectedShopEditions = [$this->communityEdition, $this->professionalEdition, $this->enterpriseEdition];
        if (!in_array($this->shopEdition, $expectedShopEditions)) {
            throw new \InvalidArgumentException(
                'Parameter $shopEdition has an unexpected value: "' . $shopEdition . '". ' .
                'Expected values are: "' . implode(',', $expectedShopEditions) . '". ',
                ErrorEnum::CODE_INVALID_SHOP_EDITION->value
            );
        }
    }

    protected function validateUnifiedNamespaceArray(array $unifiedNamespaceArray): void
    {
        if (empty($unifiedNamespaceArray)) {
            throw new \Exception(
                'No unified namespace found',
                ErrorEnum::CODE_NO_UNIFIED_NAMESPACE_FOUND->value
            );
        }
    }

    protected function validateOutputDirectoryPermissions(): void
    {
        if (!is_dir($this->outputDirectory)) {
            throw new OutputDirectoryValidationException(
                sprintf(
                    'The directory "%s" where the class files have to be written to does not exist. Please ' .
                    'create the directory "%s" with write permissions for the user "%s" and run this script again',
                    $this->outputDirectory,
                    $this->outputDirectory,
                    get_current_user()
                ),
                ErrorEnum::CODE_DIRECTORY_CREATION_ERROR->value
            );
        } elseif (!is_writable($this->outputDirectory)) {
            throw new OutputDirectoryValidationException(
                sprintf(
                    'The directory "%s" where the class files have to be written to is not writable for user ' .
                    '"%s". Please fix the permissions on this directory and run this script again',
                    realpath($this->outputDirectory),
                    get_current_user()
                ),
                ErrorEnum::CODE_DIRECTORY_CREATION_ERROR->value
            );
        }
    }

    protected function createUnifiedNamespaceSubDirectory(string $unifiedSubNamespace): string
    {
        $this->validateOutputDirectoryPermissions();

        $unifiedSubNamespacePath = Path::join($this->outputDirectory,$unifiedSubNamespace);
        $this->fileSystem->mkdir($unifiedSubNamespacePath, 0755);

        return $unifiedSubNamespacePath;
    }

    protected function getTemplatingEngine(): PhpEngine
    {
        $filesystemLoader = new FilesystemLoader($this->templateDir . '%name%');

        return new PhpEngine(new TemplateNameParser(), $filesystemLoader);
    }
}
