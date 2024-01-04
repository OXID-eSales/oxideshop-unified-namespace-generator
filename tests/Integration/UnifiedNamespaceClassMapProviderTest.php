<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\UnifiedNameSpaceGenerator\tests\Integration;

use \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMapProvider;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Class UnifiedNamespaceClasSMapProviderTest
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\tests
 */
class UnifiedNamespaceClassMapProviderTest extends \PHPUnit\Framework\TestCase
{

    const ROOT_DIRECTORY = 'root';

    /**
     * @var vfsStreamDirectory
     */
    private $vfsStreamDirectory = null;

    /**
     * Test case that the BC map is missing
     */
    public function testGetClassMapMapNotAvailable()
    {
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_noBackwardsCompatibilityMap');
        $factsMock = $this->getFactsMock('CE');
        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($factsMock);
        $unifiedNameSpaceClassMapProvider->getClassMap();
    }

    /**
     * Test case that the BC map is no array.
     */
    public function testGetClassMapBCMapNotAnArray()
    {
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_invalid');
        $factsMock = $this->getFactsMock('CE');
        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($factsMock);
        $unifiedNameSpaceClassMapProvider->getClassMap();
    }

    /**
     * @return array
     */
    public function providerClassMapsAndEditions()
    {
        return [
            'ce_edition' => [
                'CE',
                [
                    'OxidEsales\Eshop\ClassExistsOnlyInCommunityEdition'            => [
                        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsOnlyInCommunityEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
                        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsInCommunityAndProfessionalEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
                        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsInAllEditions::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
                        'editionClassName' => \OxidEsales\EshopCommunity\AbstractClassExistsInAllEditions::class,
                        'isAbstract'       => true,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ]
                ]
            ],
            'pe_edition' => [
                'PE',
                [
                    'OxidEsales\Eshop\ClassExistsOnlyInCommunityEdition'            => [
                        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsOnlyInCommunityEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
                        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInCommunityAndProfessionalEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
                        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInAllEditions::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
                        'editionClassName' => \OxidEsales\EshopProfessional\AbstractClassExistsInAllEditions::class,
                        'isAbstract'       => true,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                ]
            ],
            'ee_edition' => [
                'EE',
                [
                    'OxidEsales\Eshop\ClassExistsOnlyInCommunityEdition'            => [
                        'editionClassName' => \OxidEsales\EshopCommunity\ClassExistsOnlyInCommunityEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInCommunityAndProfessionalEdition' => [
                        'editionClassName' => \OxidEsales\EshopProfessional\ClassExistsInCommunityAndProfessionalEdition::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\ClassExistsInAllEditions'                     => [
                        'editionClassName' => \OxidEsales\EshopEnterprise\ClassExistsInAllEditions::class,
                        'isAbstract'       => false,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                    'OxidEsales\Eshop\AbstractClassExistsInAllEditions'             => [
                        'editionClassName' => \OxidEsales\EshopEnterprise\AbstractClassExistsInAllEditions::class,
                        'isAbstract'       => true,
                        'isInterface'      => false,
                        'isDeprecated'     => false
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider providerClassMapsAndEditions
     *
     * @param string $edition
     * @param array  $expectedClassMap
     */
    public function testGetClassMapValid($edition, $expectedClassMap)
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $factsMock = $this->getFactsMock($edition);

        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($factsMock);
        $this->assertEquals(
            $expectedClassMap,
            $unifiedNameSpaceClassMapProvider->getClassMap(),
            'The class map for edition ' . $edition . ' is not as expected'
        );
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
