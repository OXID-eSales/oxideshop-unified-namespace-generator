<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use Exception;
use FilesystemIterator;
use OxidEsales\EshopCommunity\Application\Model\Article;
use OxidEsales\EshopCommunity\Core\Contract\AbstractUpdatableFields;
use OxidEsales\EshopCommunity\Core\Contract\ClassNameResolverInterface;
use OxidEsales\EshopCommunity\Core\Contract\IConfigurable;
use OxidEsales\EshopCommunity\Core\FileSystem\FileSystem;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\FileSystemCompatibilityException;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException;
use OxidEsales\UnifiedNameSpaceGenerator\Generator;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Path;

final class GeneratorTest extends TestCase
{
    use VfsStreamTrait;

    private string $testOutputDir = __DIR__ . DIRECTORY_SEPARATOR . 'test_generated' . DIRECTORY_SEPARATOR;
    private string $validBasePath = __DIR__ . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'case_valid';

    private array $classMapExample = [
        'OxidEsales\Eshop\Core\Contract\AbstractUpdatableFields' => [
            'editionClassName' => AbstractUpdatableFields::class,
            'isAbstract' => true,
            'isInterface' => false,
            'isDeprecated' => false
        ],
        'OxidEsales\Eshop\Application\Model\Article' => [
            'editionClassName' => Article::class,
            'isAbstract' => false,
            'isInterface' => false,
            'isDeprecated' => true
        ],
        'OxidEsales\Eshop\Core\Contract\ClassNameResolverInterface' => [
            'editionClassName' => ClassNameResolverInterface::class,
            'isAbstract' => false,
            'isInterface' => true,
            'isDeprecated' => false
        ],
        'OxidEsales\Eshop\Core\FileSystem\FileSystem' => [
            'editionClassName' => FileSystem::class,
            'isAbstract' => false,
            'isInterface' => false,
            'isDeprecated' => false
        ],
        'OxidEsales\Eshop\Core\Contract\IConfigurable' => [
            'editionClassName' => IConfigurable::class,
            'isAbstract' => false,
            'isInterface' => true,
            'isDeprecated' => false
        ],
    ];

    private array $checkForFiles = [
        'AbstractUpdatableFields.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
            'Core' . DIRECTORY_SEPARATOR . 'Contract' . DIRECTORY_SEPARATOR .
            'AbstractUpdatableFields.php',
        'Article.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
            'Application' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR .
            'Article.php',
        'ClassNameResolverInterface.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
            'Core' . DIRECTORY_SEPARATOR . 'Contract' . DIRECTORY_SEPARATOR .
            'ClassNameResolverInterface.php',
        'FileSystem.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
            'Core' . DIRECTORY_SEPARATOR . 'FileSystem' . DIRECTORY_SEPARATOR .
            'FileSystem.php',
        'IConfigurable.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
            'Core' . DIRECTORY_SEPARATOR . 'Contract' . DIRECTORY_SEPARATOR .
            'IConfigurable.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (!is_dir($this->testOutputDir)) {
            mkdir($this->testOutputDir);
        }

        $this->removeTestResults();
    }

    protected function tearDown(): void
    {
        $this->removeTestResults();

        if (is_dir($this->testOutputDir)) {
            rmdir($this->testOutputDir);
        }

        parent::tearDown();
    }

    public function testGeneratorConstructorTargetDirectoryNotExisting(): void
    {
        $notExistingDirectory = $this->getOutputDirectory() . DIRECTORY_SEPARATOR . 'not_existing';

        $this->expectException(OutputDirectoryValidationException::class);

        new Generator($this->getUnifiedNameSpaceProviderMock(), $notExistingDirectory);
    }

    public function testGenerateCannotCreateTargetDirectory(): void
    {
        $outputDirectory = $this->getVirtualOutputDirectory();
        chmod($outputDirectory, 0000);

        $this->expectException(OutputDirectoryValidationException::class);

        (new Generator($this->getUnifiedNameSpaceProviderMock(), $outputDirectory))->generate();
    }

    public static function cleanupOutputDirectoryPermissionsDataProvider(): array
    {
        $data = [];

        // Test case that we have existing files in path that cannot be deleted.
        $data['unable_to_delete_existing_files'] = [
            'structure' =>
                ['generated' => [
                    'sub' => ['some_file.txt' => 'some_file_contents',
                        'some_other_file.txt' => 'some_other_file_contents'],
                    'emptyFolder' => []
                ]],
            'permissions' => 0444,
            'relativePath' => 'sub'
        ];

        // Test case that a sub directory cannot be deleted
        $data['unable_to_delete_directory'] = [
            'structure' =>
                ['generated' => [
                    'sub' => ['subsub' => []]
                ]],
            'permissions' => 0444,
            'relativePath' => 'sub'
        ];

        return $data;
    }

    #[DataProvider('cleanupOutputDirectoryPermissionsDataProvider')]
    public function testCleanupOutputDirectoryPermissions(
        array $structure,
        int $permissions,
        string $relativePath
    ): void {
        $outputDirectory = $this->getVirtualOutputDirectory($structure);
        /**
         * File and directory deletions are operation on the directory. So the right permission on the directory,
         * not the file have to be set
         */
        chmod($outputDirectory . $relativePath, $permissions);

        $this->expectException(IOException::class);

        (new Generator($this->getUnifiedNameSpaceProviderMock(), $outputDirectory))
            ->cleanupOutputDirectory();
    }

    public static function mapValidationErrorsDataProvider(): array
    {
        $data = [];

        // Test case, that the class maps are empty:
        $data['case_empty_class_map'] = [
            'classMap' => [],
        ];

        // Test case, that the complete class map has an invalid structure:
        $data['case_invalid_structure'] = [
            'classMap' => ['wrong key' => 'this will not work'],
        ];

        // Test cases for a malformed map:
        $invalidMap = [
            'oxarticle' => [
                'editionClassName' => Article::class,
                'isAbstract' => false,
                'isInterface' => false
            ]
        ];
        $data['case_no_unc_namespace'] = [
            'classMap' => $invalidMap,
        ];

        $invalidMap = [
            'OxidEsales\Eshop\Application\Model\Article' => [
                'aaa' => '\OxidEsales\EshopUnknown\Application\Model\Article',
                'bbb' => false,
                'ccc' => false
            ]
        ];
        $data['case_invalid_layout'] = ['classMap' => $invalidMap,];

        return $data;
    }

    #[DataProvider('mapValidationErrorsDataProvider')]
    public function testGenerateValidationErrors(array $classMap): void
    {
        $this->expectException(Exception::class);

        $this->copyTestDataIntoVirtualFileSystem('case_valid');

        (new Generator($this->getUnifiedNameSpaceProviderMock($classMap), $this->getVirtualOutputDirectory()))
            ->generate();
    }

    public function testGenerateGeneratedClassesOk(): void
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getOutputDirectory();
        (new Generator($this->getUnifiedNameSpaceProviderMock(), $outputDirectory))
            ->generate();

        // verify generated files are as expected
        foreach ($this->checkForFiles as $name => $path) {
            $resultFile = $this->assertFileExistsAfterGeneration($outputDirectory, $path);

            $expectedFileContent = trim(file_get_contents(Path::join($this->validBasePath, 'ExpectedClasses', $name)));
            $actualFileContent = trim(file_get_contents($resultFile));

            $this->assertSame(
                $expectedFileContent,
                $actualFileContent,
                "Expected and actual content of file '$name' are not the same!"
            );
        }
    }

    public function testGenerateGeneratedClassesOkMultipleRuns(): void
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getOutputDirectory();
        (new Generator($this->getUnifiedNameSpaceProviderMock(), $this->getOutputDirectory()))
            ->generate();

        // verify generated files are present but contain EE file headers.
        foreach ($this->checkForFiles as $name => $path) {
            $resultFile = $this->assertFileExistsAfterGeneration($outputDirectory, $path);

            $expectedFileContent = file_get_contents(Path::join($this->validBasePath, 'ExpectedClasses', $name));
            $actualFileContent = trim(file_get_contents($resultFile));

            $this->assertNotSame(
                $expectedFileContent,
                $actualFileContent,
                "Expected and actual content of file '$name' are unexpectedly the same!"
            );
        }

        // Now run it again but this time for CE.
        $this->testGenerateGeneratedClassesOk();
    }

    public function testGenerateCannotWriteFile(): void
    {
        /** In this case a directory named 'Article.php' is present, so the file 'Article.php' cannot be created */
        $structure = [
            'generated' => [
                'OxidEsales' => ['Eshop' => ['Application' => ['Model' => ['Article.php' => 'someFile.php']]]]
            ]
        ];

        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getVirtualOutputDirectory($structure);

        $file = $outputDirectory . 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR . 'Application' .
            DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Article.php';
        chmod($file, 0444);

        $this->expectException(FileSystemCompatibilityException::class);

        $generator = new Generator($this->getUnifiedNameSpaceProviderMock(), $outputDirectory);

        $generator->generate();
    }

    private function removeTestResults(): void
    {
        $testDir = $this->getOutputDirectory();
        $directoryIterator = new RecursiveDirectoryIterator($testDir, FilesystemIterator::SKIP_DOTS);
        $items = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
    }

    private function getOutputDirectory(): string
    {
        return Path::join(__DIR__, 'test_generated');
    }

    private function getUnifiedNameSpaceProviderMock($classMap = null): UnifiedNameSpaceClassMapProvider
    {
        if (empty($classMap) && $classMap !== []) {
            $classMap = $this->classMapExample;
        }

        $mock = $this->getMockBuilder(UnifiedNameSpaceClassMapProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassMap'])
            ->getMock();
        $mock->expects($this->any())
            ->method('getClassMap')
            ->willReturn($classMap);

        return $mock;
    }

    private function assertFileExistsAfterGeneration(string $outputDirectory, string $relativeFilePath): string
    {
        $resultFile = $outputDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;
        $this->assertFileExists($resultFile, "File '$resultFile' does not exists after file generation!");

        return $resultFile;
    }
}
