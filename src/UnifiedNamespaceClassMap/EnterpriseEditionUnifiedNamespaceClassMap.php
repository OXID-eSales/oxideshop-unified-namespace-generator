<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

use OxidEsales\EshopCommunity\Internal\Framework\Edition;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\EditionDirectoriesLocator;

/**
 * @deprecated will be removed in next major
 */
class EnterpriseEditionUnifiedNamespaceClassMap extends ProfessionalEditionUnifiedNamespaceClassMap
{
    public function __construct(
        protected string $editionText = 'OXID eShop Enterprise Edition was chosen.'
    ) {
        parent::__construct($editionText);
    }

    public function getClassMap(): array
    {
        $unifiedNamespaceClassMapProfessionalEdition = parent::getClassMap();

        $unifiedNamespaceClassMapEnterpriseEdition = $this->resolveUnifiedNamespaceClassMap(
            (new EditionDirectoriesLocator())->getEditionSourcePath(Edition::Enterprise)
        );

        return array_merge($unifiedNamespaceClassMapProfessionalEdition, $unifiedNamespaceClassMapEnterpriseEdition);
    }
}
