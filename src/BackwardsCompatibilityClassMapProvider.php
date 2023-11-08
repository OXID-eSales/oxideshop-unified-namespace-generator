<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\Facts\Facts;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidBackwardsCompatibilityClassMapException;

class BackwardsCompatibilityClassMapProvider
{
    public function __construct(private readonly Facts $facts)
    {
    }

    public function getClassMap(): array
    {
        $communityEditionSourcePath = $this->facts->getCommunityEditionSourcePath();
        $backwardsCompatibilityClassMapFile = $communityEditionSourcePath . DIRECTORY_SEPARATOR .
                                              'Core' . DIRECTORY_SEPARATOR .
                                              'Autoload' . DIRECTORY_SEPARATOR .
                                              'BackwardsCompatibilityClassMap.php';

        if (!is_readable($backwardsCompatibilityClassMapFile)) {
            throw new InvalidBackwardsCompatibilityClassMapException(
                'Backwards compatibility class map file ' . $backwardsCompatibilityClassMapFile .
                ' is not readable or does not exist',
                ErrorEnum::CODE_MISSING_BACKWARDS_COMPATIBILITY_CLASS_MAP->value
            );
        }

        $backwardsCompatibilityClassMap =
            include $backwardsCompatibilityClassMapFile;

        if (!is_array($backwardsCompatibilityClassMap)) {
            throw new InvalidBackwardsCompatibilityClassMapException(
                'Backwards compatibility class map is not an array ',
                ErrorEnum::CODE_INVALID_BACKWARDS_COMPATIBILITY_CLASS_MAP->value
            );
        }

        return array_flip($backwardsCompatibilityClassMap);
    }
}
