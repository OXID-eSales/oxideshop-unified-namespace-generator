<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\EshopCommunity\Internal\Framework\Edition;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\EditionResolver;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\CommunityEditionUnifiedNamespaceClassMap;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\EnterpriseEditionUnifiedNamespaceClassMap;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\ProfessionalEditionUnifiedNamespaceClassMap;

class UnifiedNameSpaceClassMapProvider
{
    public function getClassMap(): array
    {
        return match (
            (new EditionResolver())->getEdition()
        ) {
            Edition::Community => (new CommunityEditionUnifiedNamespaceClassMap())->getClassMap(),
            Edition::Professional => (new ProfessionalEditionUnifiedNamespaceClassMap())->getClassMap(),
            Edition::Enterprise => (new EnterpriseEditionUnifiedNamespaceClassMap())->getClassMap(),
        };
    }
}
