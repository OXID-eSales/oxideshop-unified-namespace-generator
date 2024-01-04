<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
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
