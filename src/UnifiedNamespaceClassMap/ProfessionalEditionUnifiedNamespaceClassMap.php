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
class ProfessionalEditionUnifiedNamespaceClassMap extends CommunityEditionUnifiedNamespaceClassMap
{
    public function __construct(
        protected string $editionText = 'OXID eShop Professional Edition was chosen.'
    ) {
        parent::__construct($editionText);
    }

    public function getClassMap(): array
    {
        $unifiedNamespaceClassMapCommunityEdition = parent::getClassMap();

        $unifiedNamespaceClassMapProfessionalEdition = $this->resolveUnifiedNamespaceClassMap(
            (new EditionDirectoriesLocator())->getEditionSourcePath(Edition::Professional)
        );

        return array_merge($unifiedNamespaceClassMapCommunityEdition, $unifiedNamespaceClassMapProfessionalEdition);
    }
}
