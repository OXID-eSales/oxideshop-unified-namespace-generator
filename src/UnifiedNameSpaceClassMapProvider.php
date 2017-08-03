<?php
/**
 * This file is part of OXID eSales Unified Namespaces file generation script.
 *
 * OXID eSales Unified Namespaces file generation is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales Unified Namespaces file generation script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales Unified Namespaces file generation script. If not, see <http://www.gnu.org/licenses/>.
 *
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
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
