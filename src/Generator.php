<?php
/**
 * This file is part of OXID eSales Unified Namespaces file generation script.
 *
 * OXID eSales Unified Namespaces file generation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Unified Namespaces file generation script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Unified Namespaces file generation script. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\UnifiedNameSpaceGenerator;

/**
 * Edition specific class file generator
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator
 */
class Generator
{

    /**
     * The root directory, where the class files will be written to.
     *
     * IMPORTANT: The output directory has to be kept in sync with composer.json.
     */
    protected $outputDirectory = '';


    protected $fileSystem = null;

    const COMMUNITY_EDITION = \OxidEsales\Facts\Edition\EditionSelector::COMMUNITY;
    const PROFESSIONAL_EDITION = \OxidEsales\Facts\Edition\EditionSelector::PROFESSIONAL;
    const ENTERPRISE_EDITION = \OxidEsales\Facts\Edition\EditionSelector::ENTERPRISE;
    const SMARTY_COMPILE_DIR = __DIR__ . '' . DIRECTORY_SEPARATOR . 'smarty' . DIRECTORY_SEPARATOR . 'templates_c' . DIRECTORY_SEPARATOR;
    const ERROR_CODE_FILE_DELETION_ERROR = 1;
    const ERROR_CODE_DIRECTORY_DELETION_ERROR = 2;
    const ERROR_CODE_DIRECTORY_CREATION_ERROR = 3;
    const ERROR_CODE_FILE_CREATION_ERROR = 4;
    const ERROR_CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP = 6;
    const ERROR_CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP_ENTRY = 8;
    const ERROR_CODE_INVALID_UNIFIED_CLASS_NAME = 9;
    const ERROR_CODE_INVALID_UNIFIED_NAMESPACE = 10;
    const ERROR_CODE_INVALID_SHOP_EDITION = 11;
    const ERROR_CODE_NO_UNIFIED_NAMESPACE_FOUND = 12;
    const ERROR_CODE_SMARTY_COMPILE_DIR_PERMISSIONS = 13;
    const ERROR_CODE_DIRECTORY_DELETION_TIMING_ERROR = 14;

    /** @var string OXID eShop edition */
    private $shopEdition = '';

    /** @var \OxidEsales\Facts\Facts|null */
    private $facts = null;

    /** @var \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider|null */
    private $unifiedNameSpaceClassMapProvider = null;

    /**
     * Generator constructor
     *
     * @param \OxidEsales\Facts\Facts          $facts
     * @param UnifiedNameSpaceClassMapProvider $unifiedNameSpaceClassMapProvider
     * @param string                           $outputDirectory
     */
    public function __construct(
        \OxidEsales\Facts\Facts $facts,
        \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider $unifiedNameSpaceClassMapProvider,
        $outputDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR
    ) {
        $this->facts = $facts;
        $this->unifiedNameSpaceClassMapProvider = $unifiedNameSpaceClassMapProvider;

        /** outputDirectory must not be modified after object construction */
        $this->outputDirectory = $outputDirectory;
        $this->validateOutputDirectoryPermissions();

        /** shopEdition must not be modified after object construction */
        $this->shopEdition = $facts->getEdition();
        $this->validateShopEdition($this->shopEdition);

        $this->fileSystem = new \Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * Delete the generated contents of the output directory
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function cleanupOutputDirectory()
    {
        $directoryIterator = new \RecursiveDirectoryIterator($this->outputDirectory, \RecursiveDirectoryIterator::SKIP_DOTS);

        /**
         * Items, which must not be deleted have to be skipped during the directory iteration
         *
         * @param \SplFileInfo $current
         * @return bool
         */
        $filterCallback = function (\SplFileInfo $current) {
            $skipItem = false === strpos($current->getFilename(), '.gitkeep') ? true : false;
            return $skipItem;
        };

        $filteredDirectoryIterator = new \RecursiveCallbackFilterIterator($directoryIterator, $filterCallback);
        $items = new \RecursiveIteratorIterator(
            $filteredDirectoryIterator,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $this->fileSystem->remove($items);
    }

    /**
     * Generate class files in the unified namespaces for the currently installed edition of OXID eShop
     */
    public function generate()
    {
        $classMap = $this->unifiedNameSpaceClassMapProvider->getClassMap();

        $this->generateClassFiles($classMap);
    }

    /**
     * Generate class files for a given class map
     *
     * @param array $classMap Class map for the given edition
     *
     * @throws \Exception
     */
    protected function generateClassFiles(array $classMap)
    {
        $backwardsCompatibilityMap = $this->getBackwardsCompatibilityMap();

        $unifiedNamespaceArray = $this->getUnifiedNamespaceArray($classMap);
        $this->validateUnifiedNamespaceArray($unifiedNamespaceArray);

        foreach ($unifiedNamespaceArray as $unifiedSubNamespace => $editionClassDescriptions) {
            $this->buildSubNamespace($unifiedSubNamespace, $editionClassDescriptions, $backwardsCompatibilityMap);
        }
    }

    /**
     * @return array
     */
    protected function getBackwardsCompatibilityMap()
    {
        $backwardsCompatibilityClassMapProvider = new BackwardsCompatibilityClassMapProvider($this->facts);
        return $backwardsCompatibilityClassMapProvider->getClassMap();
    }

    /**
     * Return a map of sub-namespaces and their corresponding classes including metadata
     *
     * @param array $classMap
     *
     * @return array
     */
    protected function getUnifiedNamespaceArray(array $classMap)
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

    /**
     * Delegate rendering of the contents and writing the class files for a given sub-namespace
     *
     * @param string $unifiedSubNamespace       A given unified sub-namespace
     * @param array  $editionClassDescriptions  Holds all corresponding edition class descriptions
     * @param array  $backwardsCompatibilityMap A map of backwards compatible classes to classes in the unified namespace
     *
     * @throws \Exception
     */
    protected function buildSubNamespace($unifiedSubNamespace, array $editionClassDescriptions, array $backwardsCompatibilityMap)
    {
        $unifiedSubNamespacePath = $this->createUnifiedNamespaceSubDirectory($unifiedSubNamespace);

        foreach ($editionClassDescriptions as $editionClassDescription) {
            $filePath = $unifiedSubNamespacePath . '/' . $editionClassDescription['shortUnifiedClassName'] . '.php';
            $fullyQualifiedUnifiedClass = '\\' . trim($unifiedSubNamespace . '\\' . $editionClassDescription['shortUnifiedClassName'], '\\');

            $backwardsCompatibleClass = null;
            $backwardsCompatibilityMapIndex = trim($fullyQualifiedUnifiedClass, '\\');
            if (array_key_exists($backwardsCompatibilityMapIndex, $backwardsCompatibilityMap)) {
                $backwardsCompatibleClass = $backwardsCompatibilityMap[$backwardsCompatibilityMapIndex];
            }

            $content = $this->renderContent(
                $unifiedSubNamespace,
                $editionClassDescription,
                $fullyQualifiedUnifiedClass,
                $backwardsCompatibleClass
            );

            $this->writeFile($filePath, $content);
        }
    }

    /**
     * Delegate rendering and return the content of a given class file in the unified namespace
     *
     * @param string $unifiedSubNamespace        A given unified sub-namespace
     * @param array  $editionClassDescription    Holds the description of a edition class
     * @param string $fullyQualifiedUnifiedClass Fully qualified name of the edition class
     * @param string $backwardsCompatibleClass   Class name of the corresponding backwards compatible class
     *
     * @return string
     */
    protected function renderContent($unifiedSubNamespace, $editionClassDescription, $fullyQualifiedUnifiedClass, $backwardsCompatibleClass)
    {
        $smarty = $this->getSmarty();

        $smarty->assign('shopEdition', $this->shopEdition);
        $smarty->assign('class', $editionClassDescription);
        $smarty->assign('namespace', $unifiedSubNamespace);
        $smarty->assign('fullyQualifiedUnifiedClass', $fullyQualifiedUnifiedClass);
        $smarty->assign('backwardsCompatibleClass', $backwardsCompatibleClass);

        return $smarty->fetch('class_file_template.tpl');
    }

    /**
     * Write a given content to a given file path
     *
     * @param string $filePath Path to the class file
     * @param string $content  Content of the class file
     *
     * @throws \Exception
     */
    protected function writeFile($filePath, $content)
    {
        $this->validateOutputDirectoryPermissions();

        $currentDirectory = dirname($filePath);
        if (!is_writable($currentDirectory)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\PermissionException(
                'Could not create file ' . $filePath . ' ' .
                'The directory ' . $currentDirectory . ' is not writable for user "' . get_current_user() . '". ' .
                'Please fix the permissions on this directory and run this script again.',
                static::ERROR_CODE_FILE_CREATION_ERROR
            );
        }

        $fileHandle = fopen($filePath, 'wb');
        if (!$fileHandle) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\FileSystemCompatibilityException(
                'Could not open file file handle for ' . $filePath . ' ' .
                'There might be a problem with your file system. Try to solve this problem and run this script again.',
                static::ERROR_CODE_FILE_CREATION_ERROR
            );
        }

        $result = fwrite($fileHandle, $content);
        if (false === $result) {
            fclose($fileHandle);
            throw new \Exception(
                'Could not create file ' . $filePath,
                static::ERROR_CODE_FILE_CREATION_ERROR
            );
        }
        if (0 === $result) {
            fclose($fileHandle);
            throw new \Exception(
                'Created empty file ' . $filePath,
                static::ERROR_CODE_FILE_CREATION_ERROR
            );
        }
        fclose($fileHandle);
    }

    /**
     * Validate the name of a unified sub-namespace
     *
     * @param string $unifiedSubNamespace        Name of the sub-namespace
     * @param string $fullyQualifiedUnifiedClass Full qualified name of class, where the namespace should have been
     *                                           extracted from
     *
     * @throws \Exception
     */
    protected function validateUnifiedNamespace($unifiedSubNamespace, $fullyQualifiedUnifiedClass)
    {
        if (!$unifiedSubNamespace) {
            throw new \Exception(
                'Could not extract unified sub namespace from string ' . $fullyQualifiedUnifiedClass,
                static::ERROR_CODE_INVALID_UNIFIED_NAMESPACE
            );
        }
    }

    /**
     * Validate the layout of the edition class description as defined in the current UnifiedNameSpaceClassMap.php
     * The this script has to be kept in sync with the layout of that file.
     *
     * @param array $editionClassDescription Holds the class name and metadata for a given edition class
     *
     * @throws \Exception
     */
    protected function validateEditionClassDescription(array $editionClassDescription)
    {
        $expectedKeys = ['isAbstract', 'isInterface', 'editionClassName', 'isDeprecated'];
        $message = 'Edition class description has a wrong layout. ' .
                   'It must be an non empty array with the following keys ' . implode(',', $expectedKeys) . ' ';
        $code = static::ERROR_CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP_ENTRY;

        if (empty($editionClassDescription) || !is_array($editionClassDescription)) {
            throw new \Exception($message, $code);
        }

        $actualKeys = array_keys($editionClassDescription);
        sort($expectedKeys);
        sort($actualKeys);
        if ($expectedKeys != $actualKeys) {
            $message .= ' Actual edition class description is ' . var_export($editionClassDescription, true);
            throw new \Exception($message, $code);
        }
    }

    /**
     * Validate a given short class name of a class
     *
     * @param string $shortUnifiedClassName      Short name of the class
     * @param string $fullyQualifiedUnifiedClass Full qualified name of class, where the short name should have been
     *                                           extracted from
     *
     * @throws \Exception
     */
    protected function validateShortUnifiedClassName($shortUnifiedClassName, $fullyQualifiedUnifiedClass)
    {
        if (!$shortUnifiedClassName) {
            throw new \Exception(
                'Could not extract short unified class name from string ' . $fullyQualifiedUnifiedClass,
                static::ERROR_CODE_INVALID_UNIFIED_CLASS_NAME
            );
        }
    }

    /**
     * Validate a given shop edition
     *
     * @param string $shopEdition Shop edition to validate
     */
    protected function validateShopEdition($shopEdition)
    {
        $expectedShopEditions = [static::COMMUNITY_EDITION, static::PROFESSIONAL_EDITION, static::ENTERPRISE_EDITION];
        if (!in_array($this->shopEdition, $expectedShopEditions)) {
            throw new \InvalidArgumentException(
                'Parameter $shopEdition has an unexpected value: "' . $shopEdition . '". ' .
                'Expected values are: "' . implode(',', $expectedShopEditions) . '". ',
                static::ERROR_CODE_INVALID_SHOP_EDITION
            );
        }
    }

    /**
     * Validate the array which holds all existing sub-namespaces
     *
     * @param array $unifiedNamespaceArray Holds all existing sub namespaces
     *
     * @throws \Exception
     */
    protected function validateUnifiedNamespaceArray($unifiedNamespaceArray)
    {
        if (empty($unifiedNamespaceArray)) {
            throw new \Exception(
                'No unified namespace found',
                static::ERROR_CODE_NO_UNIFIED_NAMESPACE_FOUND
            );
        }
    }

    /**
     * Validate the permission on the output directory
     *
     * @throws \Exception
     */
    protected function validateOutputDirectoryPermissions()
    {
        if (!is_dir($this->outputDirectory)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException(
                'The directory "' . $this->outputDirectory . '" where the class files have to be written to' .
                ' does not exist. ' .
                'Please create the directory "' . $this->outputDirectory . '" with write permissions for the user "' . get_current_user() . '" ' .
                'and run this script again',
                static::ERROR_CODE_DIRECTORY_CREATION_ERROR
            );
        } elseif (!is_writable($this->outputDirectory)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException(
                'The directory "' . realpath($this->outputDirectory) . '" where the class files have to be written to' .
                ' is not writable for user "' . get_current_user() . '". ' .
                'Please fix the permissions on this directory ' .
                'and run this script again',
                static::ERROR_CODE_DIRECTORY_CREATION_ERROR
            );
        }
    }

    /**
     * Create the subdirectory, where the files of a given namespace will be written to and return is path
     *
     * @param string $unifiedSubNamespace A given unified sub-namespace
     *
     * @return string
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    protected function createUnifiedNamespaceSubDirectory($unifiedSubNamespace)
    {
        $this->validateOutputDirectoryPermissions();

        $unifiedSubNamespacePath = $this->outputDirectory . str_replace('\\', DIRECTORY_SEPARATOR, $unifiedSubNamespace);
        $this->fileSystem->mkdir($unifiedSubNamespacePath, 0755);

        return $unifiedSubNamespacePath;
    }

    /**
     * Return a configured instance of smarty
     *
     * @return \Smarty
     *
     * @throws \Exception
     */
    protected function getSmarty()
    {
        $smarty = new \Smarty();

        $smarty->template_dir = realpath(
            __DIR__ . DIRECTORY_SEPARATOR .
            'smarty' . DIRECTORY_SEPARATOR .
            'templates' . DIRECTORY_SEPARATOR
        );

        $smarty->compile_dir = realpath(static::SMARTY_COMPILE_DIR);
        if (!is_dir($smarty->compile_dir) || !is_writable($smarty->compile_dir)) {
            throw new \Exception(
                'Smarty compile directory ' . static::SMARTY_COMPILE_DIR .
                ' is not writable for user "' . get_current_user() . '". ' .
                'Please fix the permissions on this directory',
                static::ERROR_CODE_SMARTY_COMPILE_DIR_PERMISSIONS
            );
        }

        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';

        return $smarty;
    }

    /**
     * Return true, if a given directory is empty. else return false.
     *
     * @param string $directory Real path to directory
     *
     * @return bool
     */
    protected function isDirectoryEmpty($directory)
    {
        $isEmpty = !(new \FilesystemIterator($directory))->valid();

        return $isEmpty;
    }
}
