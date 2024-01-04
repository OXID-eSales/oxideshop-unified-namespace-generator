<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;

/**
 * Class CommunityEditionUnifiedNamespaceClassMap
 *
 * Returns the OXID eShop Community Edition specific UnifiedNamespaceClassMap
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap
 */
class CommunityEditionUnifiedNamespaceClassMap
{

    /** @var \OxidEsales\Facts\Facts */
    protected $facts = null;

    protected $editionText = "OXID eShop Community Edition was chosen.";

    /**
     * @param \OxidEsales\Facts\Facts $facts
     */
    public function __construct(\OxidEsales\Facts\Facts $facts)
    {
        $this->facts = $facts;
    }

    /**
     * @return array The merged contents of the file UnifiedNamespaceClassMap.php of the
     *               OXID eShop Community edition
     *
     * @throws \Exception
     */
    public function getClassMap()
    {
        $communityEditionSourcePath = $this->facts->getCommunityEditionSourcePath();

        return $this->resolveUnifiedNamespaceClassMap($communityEditionSourcePath);
    }

    /**
     * @param string $pathToSourceDirectory
     *
     * @return string
     */
    protected function getFullPathFromSourceDirectoryToUnifiedNamespaceClassMap($pathToSourceDirectory)
    {
        $fullPath = $pathToSourceDirectory . DIRECTORY_SEPARATOR .
                    'Core' . DIRECTORY_SEPARATOR .
                    'Autoload' . DIRECTORY_SEPARATOR .
                    'UnifiedNameSpaceClassMap.php';

        return $fullPath;
    }

    /**
     * @param string $absolutePathToSourceDirectory
     *
     * @return array The UnifiedNamespaceClassMap
     *
     * @throws \Exception
     */
    protected function resolveUnifiedNamespaceClassMap($absolutePathToSourceDirectory)
    {
        $fullPathToUnifiedNamespaceClassMapFile = $this->getFullPathFromSourceDirectoryToUnifiedNamespaceClassMap(
            $absolutePathToSourceDirectory
        );

        if (!is_readable($fullPathToUnifiedNamespaceClassMapFile)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                $this->editionText .
                ' But the file ' . $fullPathToUnifiedNamespaceClassMapFile .
                ' is not readable or does not exist',
                \OxidEsales\UnifiedNameSpaceGenerator\Generator::ERROR_CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP
            );
        }

        $unifiedNamespaceClassMap = include $fullPathToUnifiedNamespaceClassMapFile;

        if (!is_array($unifiedNamespaceClassMap)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                $this->editionText .
                ' But the file ' . $fullPathToUnifiedNamespaceClassMapFile . ' is not an array.',
                \OxidEsales\UnifiedNameSpaceGenerator\Generator::ERROR_CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP
            );
        }

        return $unifiedNamespaceClassMap;
    }
}
