<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

use OxidEsales\Facts\Facts;

class ProfessionalEditionUnifiedNamespaceClassMap extends CommunityEditionUnifiedNamespaceClassMap
{
    public function __construct(
        protected Facts $facts,
        protected string $editionText = "OXID eShop Professional Edition was chosen."
    ) {
        parent::__construct($facts, $editionText);
    }

    public function getClassMap(): array
    {
        $unifiedNamespaceClassMapCommunityEdition = parent::getClassMap();

        $professionalEditionRootPath = $this->facts->getProfessionalEditionRootPath();
        $unifiedNamespaceClassMapProfessionalEdition = $this->resolveUnifiedNamespaceClassMap(
            $professionalEditionRootPath
        );

        return array_merge($unifiedNamespaceClassMapCommunityEdition, $unifiedNamespaceClassMapProfessionalEdition);
    }
}
