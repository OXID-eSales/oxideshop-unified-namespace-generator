<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

/**
 * Class ProfessionalEditionUnifiedNamespaceClassMap
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap
 */
class ProfessionalEditionUnifiedNamespaceClassMap extends CommunityEditionUnifiedNamespaceClassMap
{

    protected $editionText = "OXID eShop Professional Edition was chosen.";

    /**
     * @return array The merged contents of the file UnifiedNamespaceClassMap.php of
     *               OXID eShop Community and Professional editions
     *
     */
    public function getClassMap()
    {
        $unifiedNamespaceClassMapOfCommunityEdition = parent::getClassMap();

        $professionalEditionRootPath = $this->facts->getProfessionalEditionRootPath();
        $unifiedNamespaceClassMapOfProfessionalEdition = $this->resolveUnifiedNamespaceClassMap($professionalEditionRootPath);

        return array_merge($unifiedNamespaceClassMapOfCommunityEdition, $unifiedNamespaceClassMapOfProfessionalEdition);
    }
}
