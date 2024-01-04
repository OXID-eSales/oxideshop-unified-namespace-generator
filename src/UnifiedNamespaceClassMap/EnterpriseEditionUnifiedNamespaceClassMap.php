<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
