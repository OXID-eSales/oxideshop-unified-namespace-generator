<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\UnifiedNameSpaceGenerator\BackwardsCompatibilityClassMapProvider;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidBackwardsCompatibilityClassMapException;
use PHPUnit\Framework\TestCase;

class BackwardsCompatibilityClassMapProviderTest extends TestCase
{
    use VfsStreamTrait;
    use FactsMockTrait;

    public function testGetClassMapDoesNotFindClassMap(): void
    {
        $this->expectException(InvalidBackwardsCompatibilityClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_noBackwardsCompatibilityMap');
        $factsMock = $this->getFactsMock('CE');

        $backwardsCompatibilityClassMapProvider = new BackwardsCompatibilityClassMapProvider($factsMock);
        $backwardsCompatibilityClassMapProvider->getClassMap();
    }

    public function testGetClassMapDoesNotFindFindArrayInClassMap(): void
    {
        $this->expectException(InvalidBackwardsCompatibilityClassMapException::class);

        $this->copyTestDataIntoVirtualFileSystem('case_invalid');
        $factsMock = $this->getFactsMock('CE');

        $backwardsCompatibilityClassMapProvider = new BackwardsCompatibilityClassMapProvider($factsMock);
        $backwardsCompatibilityClassMapProvider->getClassMap();
    }

    public function testGetClassMapReturnsValidClassMap(): void
    {
        $this->copyTestDataIntoVirtualFileSystem('case_valid');
        $factsMock = $this->getFactsMock('CE');

        $backwardsCompatibilityClassMapProvider = new BackwardsCompatibilityClassMapProvider($factsMock);
        $this->assertEquals(
            [
                'OxidEsales\\Eshop\\Application\\Model\\Article'   => 'oxarticle',
                'OxidEsales\\Eshop\\Core\\Contract\\IConfigurable' => 'oxiconfigurable'
            ],
            $backwardsCompatibilityClassMapProvider->getClassMap()
        );
    }
}
