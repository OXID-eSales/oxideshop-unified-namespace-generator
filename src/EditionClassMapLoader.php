<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition;
use OxidEsales\EshopCommunity\Internal\Framework\Edition\EditionDirectoriesLocator;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;
use Symfony\Component\Filesystem\Path;
use function is_array;

readonly class EditionClassMapLoader
{
    public function __construct(private Edition $edition)
    {
    }

    public function load(): array
    {
        $filePath = Path::join(
            (new EditionDirectoriesLocator())->getEditionSourcePath($this->edition),
            'Core',
            'Autoload',
            'UnifiedNameSpaceClassMap.php'
        );
        if (!is_readable($filePath)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                "The file $filePath for {$this->edition->getFullEditionName()} is not readable or does not exist",
            );
        }
        $unifiedNamespaceClassMap = include $filePath;
        if (!is_array($unifiedNamespaceClassMap)) {
            throw new InvalidUnifiedNamespaceClassMapException(
                "The file $filePath for {$this->edition->getFullEditionName()} can not be loaded into array.",
            );
        }

        return $unifiedNamespaceClassMap;
    }
}
