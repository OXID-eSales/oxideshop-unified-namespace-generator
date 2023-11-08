<?php

declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap;

use OxidEsales\Facts\Facts;
use OxidEsales\UnifiedNameSpaceGenerator\ErrorEnum;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;
use Symfony\Component\Filesystem\Path;

/**
 * Class CommunityEditionUnifiedNamespaceClassMap
 *
 * Returns the OXID eShop Community Edition specific UnifiedNamespaceClassMap
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\UnifiedNamespaceClassMap
 */
class CommunityEditionUnifiedNamespaceClassMap
{
    public function __construct(
        protected Facts $facts,
        protected string $editionText = "OXID eShop Community Edition was chosen.",
    ) {
    }

    public function getClassMap(): array
    {
        $communityEditionSourcePath = $this->facts->getCommunityEditionSourcePath();

        return $this->resolveUnifiedNamespaceClassMap($communityEditionSourcePath);
    }

    protected function getFullPathFromSourceDirectoryToUnifiedNamespaceClassMap(string $pathToSourceDirectory): string
    {
        return Path::join($pathToSourceDirectory, 'Core', 'Autoload', 'UnifiedNameSpaceClassMap.php');
    }

    protected function resolveUnifiedNamespaceClassMap(string $absolutePathToSourceDirectory): array
    {
        $fullPathToUnifiedNamespaceClassMapFile = $this->getFullPathFromSourceDirectoryToUnifiedNamespaceClassMap(
            $absolutePathToSourceDirectory
        );

        if (!is_readable($fullPathToUnifiedNamespaceClassMapFile)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                $this->editionText .
                ' But the file ' . $fullPathToUnifiedNamespaceClassMapFile .
                ' is not readable or does not exist',
                ErrorEnum::CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP->value
            );
        }

        $unifiedNamespaceClassMap = include $fullPathToUnifiedNamespaceClassMapFile;

        if (!is_array($unifiedNamespaceClassMap)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                $this->editionText .
                ' But the file ' . $fullPathToUnifiedNamespaceClassMapFile . ' is not an array.',
                ErrorEnum::CODE_INVALID_UNIFIED_NAMESPACE_CLASS_MAP->value
            );
        }

        return $unifiedNamespaceClassMap;
    }
}
