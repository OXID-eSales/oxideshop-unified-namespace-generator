<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

use OxidEsales\Facts\Facts;

class EnterpriseEditionUnifiedNamespaceClassMap extends ProfessionalEditionUnifiedNamespaceClassMap
{
    public function __construct(
        protected Facts $facts,
        protected string $editionText = "OXID eShop Enterprise Edition was chosen."
    ) {
        parent::__construct($facts, $editionText);
    }

    public function getClassMap(): array
    {
        $unifiedNamespaceClassMapProfessionalEdition = parent::getClassMap();

        $enterpriseEditionRootPath = $this->facts->getEnterpriseEditionRootPath();
        $unifiedNamespaceClassMapEnterpriseEdition = $this->resolveUnifiedNamespaceClassMap($enterpriseEditionRootPath);

        return array_merge($unifiedNamespaceClassMapProfessionalEdition, $unifiedNamespaceClassMapEnterpriseEdition);
    }
}
