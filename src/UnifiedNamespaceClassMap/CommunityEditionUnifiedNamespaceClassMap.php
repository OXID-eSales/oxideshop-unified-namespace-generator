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
