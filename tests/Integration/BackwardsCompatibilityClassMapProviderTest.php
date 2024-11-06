<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\UnifiedNameSpaceGenerator\BackwardsCompatibilityClassMapProvider;
use PHPUnit\Framework\TestCase;

final class BackwardsCompatibilityClassMapProviderTest extends TestCase
{
    public function testGetClassMapReturnsValidClassMap(): void
    {
        $backwardsCompatibilityClassMapProvider = (new BackwardsCompatibilityClassMapProvider())->getClassMap();
        $this->assertArrayHasKey(
            'OxidEsales\\Eshop\\Application\\Model\\Article',
            $backwardsCompatibilityClassMapProvider
        );
        $this->assertContains(
            'oxarticle',
            $backwardsCompatibilityClassMapProvider
        );
    }
}
