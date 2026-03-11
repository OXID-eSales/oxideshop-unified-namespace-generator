<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\Facts\Facts;

trait FactsMockTrait
{
    private function getFactsMock(string $edition = 'CE'): Facts
    {
        $root = $this->getVirtualFilesystemRootPath();

        $stub = $this->createStub(Facts::class);
        $stub->method('getEdition')
            ->willReturn($edition);
        $stub->method('getShopRootPath')
            ->willReturn($root);
        $stub->method('getCommunityEditionSourcePath')
            ->willReturn($root . 'vendor/oxid-esales/oxideshop-ce/source');
        $stub->method('getProfessionalEditionRootPath')
            ->willReturn($root . 'vendor/oxid-esales/oxideshop-pe');
        $stub->method('getEnterpriseEditionRootPath')
            ->willReturn($root . 'vendor/oxid-esales/oxideshop-ee');

        return $stub;
    }
}
