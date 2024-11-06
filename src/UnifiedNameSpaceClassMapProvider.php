<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition;
use OxidEsales\EshopCommunity\Internal\Framework\Edition\EditionResolver;

class UnifiedNameSpaceClassMapProvider
{
    public function getClassMap(): array
    {
        return match (
            (new EditionResolver())->getEdition()
        ) {
            Edition::Community => (new EditionClassMapLoader(Edition::Community))->load(),
            Edition::Professional => array_merge(
                (new EditionClassMapLoader(Edition::Community))->load(),
                (new EditionClassMapLoader(Edition::Professional))->load(),
            ),
            Edition::Enterprise => array_merge(
                (new EditionClassMapLoader(Edition::Community))->load(),
                (new EditionClassMapLoader(Edition::Professional))->load(),
                (new EditionClassMapLoader(Edition::Enterprise))->load(),
            ),
        };
    }
}
