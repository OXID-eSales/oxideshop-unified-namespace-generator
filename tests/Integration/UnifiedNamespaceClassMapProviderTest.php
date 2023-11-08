<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMapProvider;
use PHPUnit\Framework\TestCase;

class UnifiedNamespaceClassMapProviderTest extends TestCase
{
    use VfsStreamTrait;
    use FactsMockTrait;

    public function testGetClassMapMapNotAvailable(): void
    {
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_noBackwardsCompatibilityMap');
        $factsMock = $this->getFactsMock('CE');
        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($factsMock);
        $unifiedNameSpaceClassMapProvider->getClassMap();
    }

    public function testGetClassMapBCMapNotAnArray(): void
    {
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_invalid');
        $factsMock = $this->getFactsMock('CE');
        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($factsMock);
        $unifiedNameSpaceClassMapProvider->getClassMap();
    }

    //phpcs:disable
    public function providerClassMapsAndEditions(): array
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
     */
    public function testGetClassMapValid(string $edition, array $expectedClassMap): void
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
}
