<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;

class BackwardsCompatibilityClassMapProvider
{
    public function getClassMap(): array
    {
        return array_flip((new BasicContext())->getBackwardsCompatibilityClassMap());
    }
}
