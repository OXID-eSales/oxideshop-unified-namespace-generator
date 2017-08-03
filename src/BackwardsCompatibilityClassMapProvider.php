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

/**
 * Provides a map of classes in the unified namespace to backwards compatible classes
 */
class BackwardsCompatibilityClassMapProvider
{

    const ERROR_CODE_MISSING_BACKWARDS_COMPATIBILITY_CLASS_MAP = 5;
    const ERROR_CODE_INVALID_BACKWARDS_COMPATIBILITY_CLASS_MAP = 7;

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
     * Return a map of classes in the unified namespace to backwards compatibility classes (e.g. oxArticle)
     *
     * @return array|bool
     *
     * @throws \Exception
     */
    public function getClassMap()
    {
        $communityEditionSourcePath = $this->facts->getCommunityEditionSourcePath();
        $backwardsCompatibilityClassMapFile = $communityEditionSourcePath . DIRECTORY_SEPARATOR .
                                              'Core' . DIRECTORY_SEPARATOR .
                                              'Autoload' . DIRECTORY_SEPARATOR .
                                              'BackwardsCompatibilityClassMap.php';

        if (!is_readable($backwardsCompatibilityClassMapFile)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidBackwardsCompatibilityClassMapException(
                'Backwards compatibility class map file ' . $backwardsCompatibilityClassMapFile .
                ' is not readable or does not exist',
                static::ERROR_CODE_MISSING_BACKWARDS_COMPATIBILITY_CLASS_MAP
            );
        }

        $backwardsCompatibilityClassMap =
            include $backwardsCompatibilityClassMapFile;

        if (!is_array($backwardsCompatibilityClassMap)) {
            throw new \OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidBackwardsCompatibilityClassMapException(
                'Backwards compatibility class map is not an array ',
                static::ERROR_CODE_INVALID_BACKWARDS_COMPATIBILITY_CLASS_MAP
            );
        }

        return array_flip($backwardsCompatibilityClassMap);
    }
}
