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

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

/**
 * Class EnterpriseEditionUnifiedNamespaceClassMap
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap
 */
class EnterpriseEditionUnifiedNamespaceClassMap extends ProfessionalEditionUnifiedNamespaceClassMap
{

    protected $editionText = "OXID eShop Enterprise Edition was chosen.";

    /**
     * @return array The merged contents of the file UnifiedNamespaceClassMap.php of
     *               OXID eShop Community, Professional and Enterprise editions
     *
     */
    public function getClassMap()
    {
        $unifiedNamespaceClassMapOfProfessionalEdition = parent::getClassMap();

        $enterpriseEditionRootPath = $this->facts->getEnterpriseEditionRootPath();
        $unifiedNamespaceClassMapOfEnterpriseEdition = $this->resolveUnifiedNamespaceClassMap($enterpriseEditionRootPath);

        return array_merge($unifiedNamespaceClassMapOfProfessionalEdition, $unifiedNamespaceClassMapOfEnterpriseEdition);
    }
}
