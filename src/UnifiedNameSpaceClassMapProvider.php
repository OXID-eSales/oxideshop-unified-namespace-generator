<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\Facts\Facts;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\CommunityEditionUnifiedNamespaceClassMap;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\ProfessionalEditionUnifiedNamespaceClassMap;
use OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\EnterpriseEditionUnifiedNamespaceClassMap;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidEditionException;

class UnifiedNameSpaceClassMapProvider
{
    public function __construct(private readonly Facts $facts)
    {
    }

    public function getClassMap(): array
    {
        $shopEdition = $this->facts->getEdition();
        $unifiedNamespaceClassMap = null;

        switch ($shopEdition) {
            case EditionSelector::COMMUNITY:
                $unifiedNamespaceClassMap =
                    new CommunityEditionUnifiedNamespaceClassMap($this->facts);
                break;
            case EditionSelector::PROFESSIONAL:
                $unifiedNamespaceClassMap =
                    new ProfessionalEditionUnifiedNamespaceClassMap($this->facts);
                break;
            case EditionSelector::ENTERPRISE:
                $unifiedNamespaceClassMap =
                    new EnterpriseEditionUnifiedNamespaceClassMap($this->facts);
        }

        if (is_null($unifiedNamespaceClassMap)) {
            throw new InvalidEditionException(
                'The OXID eShop edition could not be detected. Be sure to setup your OXID eShop correctly.'
            );
        }

        return $unifiedNamespaceClassMap->getClassMap();
    }
}
