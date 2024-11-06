<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\EshopCommunity;
use OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition;
use OxidEsales\EshopEnterprise;
use OxidEsales\EshopProfessional;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider;
use PHPUnit\Framework\TestCase;

final class UnifiedNamespaceClassMapProviderTest extends TestCase
{
    public function testGetClassMap(): void
    {
        $classMap = (new UnifiedNameSpaceClassMapProvider())->getClassMap();

        match ((new EshopCommunity\Internal\Transition\Utility\BasicContext())->getEdition()) {
            Edition::Community => $this->makeCeAssertion($classMap),
            Edition::Professional => $this->makePeAssertion($classMap),
            Edition::Enterprise => $this->makeEeAssertion($classMap),
        };
    }

    private function makeCeAssertion(array $classMap): void
    {
        $this->assertEquals(
            \OxidEsales\EshopCommunity\Application\Model\Article::class,
            $classMap[Article::class]['editionClassName']
        );
    }

    private function makePeAssertion(array $classMap): void
    {
        $this->assertEquals(
            \OxidEsales\EshopProfessional\Application\Model\Article::class,
            $classMap[Article::class]['editionClassName']
        );
    }

    private function makeEeAssertion(array $classMap): void
    {
        $this->assertEquals(
            \OxidEsales\EshopEnterprise\Application\Model\Article::class,
            $classMap[Article::class]['editionClassName']
        );
    }
}
