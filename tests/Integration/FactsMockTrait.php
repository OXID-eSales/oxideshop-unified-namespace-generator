<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\Facts\Facts;
use PHPUnit\Framework\MockObject\MockObject;

trait FactsMockTrait
{
    private function getFactsMock(string $edition = 'CE'): Facts|MockObject
    {
        $mock = $this->getMockBuilder(Facts::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEdition', 'getShopRootPath'])
            ->getMock();
        $mock->method('getEdition')->willReturn($edition);
        $mock->method('getShopRootPath')->willReturn($this->getVirtualFilesystemRootPath());

        return $mock;
    }
}
