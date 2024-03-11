<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use FilesystemIterator;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\FileSystemCompatibilityException;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException;
use OxidEsales\UnifiedNameSpaceGenerator\Generator;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

class GeneratorTest extends TestCase
{
    use VfsStreamTrait;
    use FactsMockTrait;

    private string $testOutputDir = __DIR__ . DIRECTORY_SEPARATOR . 'test_generated' . DIRECTORY_SEPARATOR;
    private string $validBasePath = __DIR__ . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'case_valid';

    private array $classMapExample = [
        'OxidEsales\Eshop\Core\Contract\AbstractUpdatableFields'    => [
            'editionClassName' => \OxidEsales\EshopCommunity\Core\Contract\AbstractUpdatableFields::class,
            'isAbstract'       => true,
            'isInterface'      => false,
            'isDeprecated'     => false
        ],
        'OxidEsales\Eshop\Application\Model\Article'                => [
            'editionClassName' => \OxidEsales\EshopCommunity\Application\Model\Article::class,
            'isAbstract'       => false,
            'isInterface'      => false,
            'isDeprecated'     => true
        ],
        'OxidEsales\Eshop\Core\Contract\ClassNameResolverInterface' => [
            'editionClassName' => \OxidEsales\EshopCommunity\Core\Contract\ClassNameResolverInterface::class,
            'isAbstract'       => false,
            'isInterface'      => true,
            'isDeprecated'     => false
        ],
        'OxidEsales\Eshop\Core\FileSystem\FileSystem'               => [
            'editionClassName' => \OxidEsales\EshopCommunity\Core\FileSystem\FileSystem::class,
            'isAbstract'       => false,
            'isInterface'      => false,
            'isDeprecated'     => false
        ],
        'OxidEsales\Eshop\Core\Contract\IConfigurable'              => [
            'editionClassName' => \OxidEsales\EshopCommunity\Core\Contract\IConfigurable::class,
            'isAbstract'       => false,
            'isInterface'      => true,
            'isDeprecated'     => false
        ],
    ];

    private array $checkForFiles = [
        'AbstractUpdatableFields.php'    => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
                                            'Core' . DIRECTORY_SEPARATOR . 'Contract' . DIRECTORY_SEPARATOR .
                                            'AbstractUpdatableFields.php',
        'Article.php'                    => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
                                            'Application' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR .
                                            'Article.php',
        'ClassNameResolverInterface.php' => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
                                            'Core' . DIRECTORY_SEPARATOR . 'Contract' . DIRECTORY_SEPARATOR .
                                            'ClassNameResolverInterface.php',
        'FileSystem.php'                 => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
                                            'Core' . DIRECTORY_SEPARATOR . 'FileSystem' . DIRECTORY_SEPARATOR .
                                            'FileSystem.php',
        'IConfigurable.php'              => 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR .
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
        $this->assertFalse(is_dir($notExistingDirectory));

        $this->setExpectedExceptionOutputDirectoryValidationException();

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $this->createGenerator($factsMock, $providerMock, $notExistingDirectory);
    }

    public static function cleanupOutputDirectoryPermissionsDataProvider(): array
    {
        $data = [];

        // Test case that we have existing files in path that cannot be deleted.
        $data['unable_to_delete_existing_files'] = [
            'structure'     =>
                ['generated' => [
                    'sub'         => ['some_file.txt'       => 'some_file_contents',
                                      'some_other_file.txt' => 'some_other_file_contents'],
                    'emptyFolder' => []
                ]],
            'permissions'   => 0444,
            'relative_path' => 'sub'
        ];

        // Test case that a sub directory cannot be deleted
        $data['unable_to_delete_directory'] = [
            'structure'     =>
                ['generated' => [
                    'sub' => ['subsub' => []]
                ]],
            'permissions'   => 0444,
            'relative_path' => 'sub'
        ];

        return $data;
    }

    /**
     * @dataProvider cleanupOutputDirectoryPermissionsDataProvider
     */
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

        $this->expectException(\Symfony\Component\Filesystem\Exception\IOException::class);

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->cleanupOutputDirectory();
    }

    public static function mapValidationErrorsDataProvider(): array
    {
        $data = [];

        // Test case, that the class maps are empty:
        $data['case_empty_class_map'] = [
            'classMap'         => [],
            'exceptionMessage' => 'No unified namespace found'
        ];

        // Test case, that the complete class map has an invalid structure:
        $data['case_invalid_structure'] = [
            'classMap'         => ['wrong key' => 'this will not work'],
            'exceptionMessage' => 'Could not extract short unified class name from string'
        ];

        // Test cases for a malformed map:
        $invalidMap = [
            'oxarticle' => [
                'editionClassName' => \OxidEsales\EshopCommunity\Application\Model\Article::class,
                'isAbstract'       => false,
                'isInterface'      => false
            ]
        ];
        $data['case_no_unc_namespace'] = [
            'classMap'         => $invalidMap,
            'exceptionMessage' => 'Could not extract unified sub namespace from string oxarticle'];

        $invalidMap = [
            'OxidEsales\Eshop\Application\Model\Article' => [
                'aaa' => '\OxidEsales\EshopUnknown\Application\Model\Article',
                'bbb' => false,
                'ccc' => false
            ]
        ];
        $data['case_invalid_layout'] = ['classMap'         => $invalidMap,
                                        'exceptionMessage' => 'Edition class description has a wrong layout'];

        return $data;
    }

    /**
     * @dataProvider mapValidationErrorsDataProvider
     */
    public function testGenerateValidationErrors(array $classMap, string $exceptionMessage): void
    {
        $this->expectException(\Exception::class, $exceptionMessage);

        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock, $classMap);

        $generator = $this->createGenerator($factsMock, $providerMock, $this->getVirtualOutputDirectory());
        $generator->generate();
    }

    public function testGenerateInvalidShopEdition(): void
    {
        $this->expectException(\Exception::class, 'Parameter $shopEdition has an unexpected value: "XX"');

        $factsMock = $this->getFactsMock('XX');
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $this->createGenerator($factsMock, $providerMock, $this->getVirtualOutputDirectory());
    }

    public function testGenerateGeneratedClassesOk(): void
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getOutputDirectory();
        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->generate();

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
        $factsMock = $this->getFactsMock('EE');
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $this->getOutputDirectory());
        $generator->generate();

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

    public function testGenerateCannotCreateTargetDirectory(): void
    {
        $outputDirectory = $this->getVirtualOutputDirectory();
        chmod($outputDirectory, 0000);

        $this->setExpectedExceptionOutputDirectoryValidationException();

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->generate();
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

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        /**
         * Suppress warning, which is raised during fopen() on vfsStreamDirectory and as a warning cannot be handled in the code.
         * It would lead to wrong behavior of the test, as FileSystemCompatibilityException would not be thrown.
         */
        @$generator->generate();
    }

    private function removeTestResults(): void
    {
        $testDir = $this->getOutputDirectory();
        $directoryIterator = new \RecursiveDirectoryIterator($testDir, FilesystemIterator::SKIP_DOTS);
        $items = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

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

    protected function getUnifiedNameSpaceProviderMock(Facts|MockObject $facts, $classMap = null): UnifiedNameSpaceClassMapProvider
    {
        if (empty($classMap) && [] !== $classMap) {
            $classMap = $this->classMapExample;
        }

        $mock = $this->getMockBuilder(UnifiedNameSpaceClassMapProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassMap'])
            ->getMock();
        $mock->expects($this->any())->method('getClassMap')->willReturn($classMap);

        return $mock;
    }

    private function createGenerator($facts, $provider, $outputDirectory): Generator
    {
        return new Generator($facts, $provider, $outputDirectory);
    }

    private function setExpectedExceptionOutputDirectoryValidationException(): void
    {
        $this->expectException(OutputDirectoryValidationException::class);
    }

    private function assertFileExistsAfterGeneration(string $outputDirectory, string $relativeFilePath): string
    {
        $resultFile = $outputDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;
        $this->assertTrue(file_exists($resultFile), "File '$resultFile' does not exists after file generation!");

        return $resultFile;
    }
}
