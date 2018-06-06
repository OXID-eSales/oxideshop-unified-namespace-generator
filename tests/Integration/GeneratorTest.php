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

namespace OxidEsales\UnifiedNameSpaceGenerator\tests\Integration;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Class TestGenerator
 * Helper class for test
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\tests
 */
class TestGenerator extends \OxidEsales\UnifiedNameSpaceGenerator\Generator
{

    const SMARTY_COMPILE_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'not_existing_smarty_compile_directory';
}

/**
 * Class GeneratorIntegrationTest
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\tests
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{

    const TEST_OUTPUT_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'test_generated' . DIRECTORY_SEPARATOR;
    const ROOT_DIRECTORY = 'root';

    private $validBasePath = __DIR__ . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . 'case_valid';

    /**
     * @var vfsStreamDirectory
     */
    private $vfsStreamDirectory = null;

    /**
     * @var array Example class map for testing.
     */
    private $classMapExample = [
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

    /**
     * @var array Expected files.
     */
    private $checkForFiles = [
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

    /**
     * Fixture set up.
     */
    protected function setUp()
    {
        parent::setUp();

        if (!is_dir(self::TEST_OUTPUT_DIR)) {
            mkdir(self::TEST_OUTPUT_DIR);
        }

        $this->removeTestResults();
    }

    /**
     * Fixture tear down
     */
    protected function tearDown()
    {
        $this->removeTestResults();

        if (is_dir(self::TEST_OUTPUT_DIR)) {
            rmdir(self::TEST_OUTPUT_DIR);
        }

        parent::tearDown();
    }

    /**
     * Test case that the main target directory does not exist
     */
    public function testGeneratorConstructorTargetDirectoryNotExisting()
    {
        $notExistingDirectory = $this->getOutputDirectory() . DIRECTORY_SEPARATOR . 'not_existing';
        $this->assertFalse(is_dir($notExistingDirectory));

        $this->setExpectedExceptionOutputDirectoryValidationException();

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $this->createGenerator($factsMock, $providerMock, $notExistingDirectory);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function providerCleanupOutputDirectoryPermissions()
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
     * Test cases that output directory cannot be cleaned due to permission issues.
     *
     * @dataProvider providerCleanupOutputDirectoryPermissions
     *
     * @param array  $structure
     * @param int    $permissions
     * @param string $relativePath
     */
    public function testCleanupOutputDirectoryPermissions($structure, $permissions, $relativePath)
    {
        $outputDirectory = $this->getVirtualOutputDirectory(0777, $structure);
        /**
         * File and directory deletions are operation on the directory. So the right permission on the directory,
         * not the file have to be set
         */
        chmod($outputDirectory . $relativePath, $permissions);

        $this->setExpectedException(\Symfony\Component\Filesystem\Exception\IOException::class);

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->cleanupOutputDirectory();
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function providerTestMapValidationErrors()
    {
        $data = [];

        // Test case, that the class maps are empty:
        $data['case_empty_class_map'] = [
            'classMap'         => [],
            'exceptionMessage' => 'No unified namespace found'
        ];

        // Test case, that the complete class map has an invalid structure:
        $data['case_invalid_structure'] = [
            'classMap'         => ['this will not work'],
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
     * Test class map validation errors.
     *
     * @dataProvider providerTestMapValidationErrors
     *
     * @param array  $classMap
     * @param string $exceptionClass
     * @param string $exceptionMessage
     */
    public function testGenerateValidationErrors($classMap, $exceptionMessage)
    {
        $this->setExpectedException(\Exception::class, $exceptionMessage);

        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock, $classMap);

        $generator = $this->createGenerator($factsMock, $providerMock, $this->getVirtualOutputDirectory());
        $generator->generate();
    }

    /**
     * Test invalid shop edition.
     */
    public function testGenerateInvalidShopEdition()
    {
        $this->setExpectedException(\Exception::class, 'Parameter $shopEdition has an unexpected value: "XX"');

        $factsMock = $this->getFactsMock('XX');
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $this->createGenerator($factsMock, $providerMock, $this->getVirtualOutputDirectory());
    }

    /**
     * All is well case testing the full integration.
     * Writes files to actual filesystem.
     */
    public function testGenerateGeneratedClassesOk()
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

            $expectedFileContent = trim(file_get_contents($this->validBasePath . DIRECTORY_SEPARATOR . 'ExpectedClasses' . DIRECTORY_SEPARATOR . $name));
            $actualFileContent = trim(file_get_contents($resultFile));

            $this->assertSame($expectedFileContent, $actualFileContent, "Expected and actual content of file '$name' are not the same!");
        }
    }

    /**
     * All is well case testing the full integration.
     * Running twice, verify shop edition switch does work.
     * Writes files to actual filesystem.
     */
    public function testGenerateGeneratedClassesOkMultipleRuns()
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

            $expectedFileContent = file_get_contents($this->validBasePath . DIRECTORY_SEPARATOR . 'ExpectedClasses' . DIRECTORY_SEPARATOR . $name);
            $actualFileContent = trim(file_get_contents($resultFile));

            $this->assertNotSame($expectedFileContent, $actualFileContent, "Expected and actual content of file '$name' are unexpectedly the same!");
            $this->assertContains('OXID eShop EE', file_get_contents($resultFile));
        }

        // Now run it again but this time for CE.
        $this->testGenerateGeneratedClassesOk();
    }

    /**
     * Test case that the target directory can not be created during file generation.
     * NOTE: We first create the generator object with a writable directory and then change permissions before
     *       calling Generator::generate().
     */
    public function testGenerateCannotCreateTargetDirectory()
    {
        $outputDirectory = $this->getVirtualOutputDirectory(0777);
        chmod($outputDirectory, 0000);

        $this->setExpectedExceptionOutputDirectoryValidationException();

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->generate();
    }

    /**
     * Test case that the output file cannot be written because a not overridable one happens to be present.
     */
    public function testGenerateCannotWriteFile()
    {
        /** In this case a directory named 'Article.php' is present, so the file 'Article.php' cannot be created */
        $structure = [
            'generated' => [
                'OxidEsales' => ['Eshop' => ['Application' => ['Model' => ['Article.php' => 'someFile.php']]]]
            ]
        ];

        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getVirtualOutputDirectory(0777, $structure);

        $file = $outputDirectory . 'OxidEsales' . DIRECTORY_SEPARATOR . 'Eshop' . DIRECTORY_SEPARATOR . 'Application' .
                DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Article.php';
        chmod($file, 0444);

        $this->setExpectedException(\OxidEsales\UnifiedNameSpaceGenerator\Exceptions\FileSystemCompatibilityException::class);

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = $this->createGenerator($factsMock, $providerMock, $outputDirectory);
        /**
         * Suppress warning, which is raised during fopen() on vfsStreamDirectory and as a warning cannot be handled in the code.
         * It would lead to wrong behavior of the test, as FileSystemCompatibilityException would not be thrown.
         */
        @$generator->generate();
    }

    /**
     * Test the case that the smarty compile directory is not accessible.
     */
    public function testSmartyCompileDirectoryNotAccessible()
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $outputDirectory = $this->getOutputDirectory();

        $message = 'Smarty compile directory ' . \OxidEsales\UnifiedNameSpaceGenerator\tests\Integration\TestGenerator::SMARTY_COMPILE_DIR .
                   ' is not writable for user';
        $this->setExpectedException(\Exception::class, $message);

        $factsMock = $this->getFactsMock();
        $providerMock = $this->getUnifiedNameSpaceProviderMock($factsMock);

        $generator = new \OxidEsales\UnifiedNameSpaceGenerator\tests\Integration\TestGenerator($factsMock, $providerMock, $outputDirectory);
        $generator->generate();
    }

    /**
     * Test helper for cleaning up.
     */
    private function removeTestResults()
    {
        $testDir = $this->getOutputDirectory();
        $directoryIterator = new \RecursiveDirectoryIterator($testDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $items = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
    }

    /**
     * Get path to output directory.
     *
     * @return string
     */
    private function getOutputDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'test_generated' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param \OxidEsales\Facts\Facts $facts
     *
     * @return \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider
     */
    protected function getUnifiedNameSpaceProviderMock(\OxidEsales\Facts\Facts $facts, $classMap = null)
    {
        if (empty($classMap) && [] !== $classMap) {
            $classMap = $this->classMapExample;
        }

        /** @var \PHPUnit_Framework_MockObject_MockObject|\OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider $mock */
        $mock = $this->getMock(
            \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider::class,
            ['getClassMap'],
            [$facts]
        );
        $mock->expects($this->any())->method('getClassMap')->willReturn($classMap);

        return $mock;
    }

    /**
     * Create the Unified Namespace Generator.
     *
     * @param \OxidEsales\Facts\Facts                                                $facts           The Facts object.
     * @param \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider $provider        The Unified Namespace class map provider.
     * @param string                                                                 $outputDirectory The directory in which the generator writes.
     *
     * @return \OxidEsales\UnifiedNameSpaceGenerator\Generator
     */
    private function createGenerator($facts, $provider, $outputDirectory)
    {
        return new \OxidEsales\UnifiedNameSpaceGenerator\Generator($facts, $provider, $outputDirectory);
    }

    /**
     * Set the expected exception, that the output directory is not writable.
     */
    private function setExpectedExceptionOutputDirectoryValidationException()
    {
        $this->setExpectedException(\OxidEsales\UnifiedNameSpaceGenerator\Exceptions\OutputDirectoryValidationException::class);
    }

    /**
     * Assert, that a wished file exists after the generation.
     *
     * @param string $outputDirectory  The generator output directory.
     * @param string $relativeFilePath The relative path of the file.
     *get
     * @return string The complete path of the file.
     */
    private function assertFileExistsAfterGeneration($outputDirectory, $relativeFilePath)
    {
        $resultFile = $outputDirectory . DIRECTORY_SEPARATOR . $relativeFilePath;
        $this->assertTrue(file_exists($resultFile), "File '$resultFile' does not exists after file generation!");

        return $resultFile;
    }

    /**
     * Get path to virtual output directory and copy the give structure $structure inside
     *
     * @todo Code is duplicated in other test classes. Either move to separate class or use testing library
     *
     * @param int   $permissions Directory permissions
     * @param array $structure   Optional directory structure to create inside output folder
     *
     * @return string
     */
    private function getVirtualOutputDirectory($permissions = 0777, $structure = null)
    {
        if (!is_array($structure)) {
            $structure = ['generated' => []];
        }

        vfsStream::create($structure, $this->getVirtualFileSystem());
        $directory = $this->getVirtualFilesystemRootPath() . 'generated';
        chmod($directory, $permissions);

        return $directory . DIRECTORY_SEPARATOR;
    }

    /**
     * @todo Code is duplicated in other test classes. Either move to separate class or use testing library
     *
     * @param string $edition The OXID eShop Edition, which the facts should give back.
     *
     * @return \OxidEsales\Facts\Facts
     */
    private function getFactsMock($edition = 'CE')
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\OxidEsales\Facts\Facts $mock */
        $mock = $this->getMockBuilder(\OxidEsales\Facts\Facts::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getEdition',
                    'getShopRootPath'
                ]
            )
            ->getMock();
        $mock->method('getEdition')->willReturn($edition);
        $mock->method('getShopRootPath')->willReturn($this->getVirtualFilesystemRootPath());

        return $mock;
    }

    /**
     * @todo Code is duplicated in other test classes. Either move to separate class or use testing library
     *
     * @return vfsStreamDirectory
     */
    private function getVirtualFileSystem()
    {
        if (is_null($this->vfsStreamDirectory)) {
            $this->vfsStreamDirectory = vfsStream::setup(self::ROOT_DIRECTORY);
        }

        return $this->vfsStreamDirectory;
    }

    /**
     * @todo Code is duplicated in other test classes. Either move to separate class or use testing library
     *
     * @param string $testCaseDirectory The directory within the pysical directory testData you want to
     *                                  load into the file system
     */
    private function copyTestDataIntoVirtualFileSystem($testCaseDirectory)
    {
        try {
            $pathToTestData = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testData' . DIRECTORY_SEPARATOR . $testCaseDirectory;
            $virtualFileSystem = $this->getVirtualFileSystem();

            vfsStream::copyFromFileSystem(
                $pathToTestData,
                $virtualFileSystem
            );
        } catch (\InvalidArgumentException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Returns the root url. It should be treated as usual file path.
     *
     * @todo Code is duplicated in other test classes. Either move to separate class or use testing library
     *
     * @return string
     */
    private function getVirtualFilesystemRootPath()
    {
        return vfsStream::url(self::ROOT_DIRECTORY) . DIRECTORY_SEPARATOR;
    }
}
