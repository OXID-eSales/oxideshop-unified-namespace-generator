<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\UnifiedNameSpaceGenerator;

use \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\CommunityEditionUnifiedNamespaceClassMap;
use \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\ProfessionalEditionUnifiedNamespaceClassMap;
use \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap\EnterpriseEditionUnifiedNamespaceClassMap;
use \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidEditionException;

/**
 * Provides a map of classes in the unified namespace to edition class names depending on the shop's current edition.
 */
class UnifiedNameSpaceClassMapProvider
{

    /** @var \OxidEsales\Facts\Facts */
    private $facts = null;

    /**
     * @param \OxidEsales\Facts\Facts $facts
     */
    public function __construct(\OxidEsales\Facts\Facts $facts)
    {
        $this->facts = $facts;
    }

    /**
     * Return an array, which is mapping unified namespace class name as key to real edition namespace class name.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getClassMap()
    {
        $shopEdition = $this->facts->getEdition();
        $unifiedNamespaceClassMap = null;

        switch ($shopEdition) {
            case \OxidEsales\UnifiedNameSpaceGenerator\Generator::COMMUNITY_EDITION:
                $unifiedNamespaceClassMap =
                    new CommunityEditionUnifiedNamespaceClassMap($this->facts);
                break;
            case \OxidEsales\UnifiedNameSpaceGenerator\Generator::PROFESSIONAL_EDITION:
                $unifiedNamespaceClassMap =
                    new ProfessionalEditionUnifiedNamespaceClassMap($this->facts);
                break;
            case \OxidEsales\UnifiedNameSpaceGenerator\Generator::ENTERPRISE_EDITION:
                $unifiedNamespaceClassMap =
                    new EnterpriseEditionUnifiedNamespaceClassMap($this->facts);
        }

        if (is_null($unifiedNamespaceClassMap)) {
            throw new InvalidEditionException(
                'The OXID eShop edition could not be detected. Be sure to setup your OXID eShop correctly.'
            );
        }

        $editionSpecificUnifiedNamespaceClassMap = $unifiedNamespaceClassMap->getClassMap();

        return $editionSpecificUnifiedNamespaceClassMap;
    }
}
