<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Filesystem\Path;

trait VfsStreamTrait
{
    private string $rootDirectory = 'root';
    private ?vfsStreamDirectory $vfsStreamDirectory = null;

    private function getVirtualFileSystem(): ?vfsStreamDirectory
    {
        if (is_null($this->vfsStreamDirectory)) {
            $this->vfsStreamDirectory = vfsStream::setup($this->rootDirectory);
        }

        return $this->vfsStreamDirectory;
    }

    private function getVirtualOutputDirectory(array $structure = null): string
    {
        if (!is_array($structure)) {
            $structure = ['generated' => []];
        }

        vfsStream::create($structure, $this->getVirtualFileSystem());
        $directory = $this->getVirtualFilesystemRootPath() . 'generated';
        chmod($directory, 0777);

        return $directory . DIRECTORY_SEPARATOR;
    }

    private function copyTestDataIntoVirtualFileSystem($testCaseDirectory): void
    {
        try {
            $pathToTestData = Path::join(dirname(__FILE__), 'testData', $testCaseDirectory);
            $virtualFileSystem = $this->getVirtualFileSystem();

            vfsStream::copyFromFileSystem(
                $pathToTestData,
                $virtualFileSystem
            );
        } catch (\InvalidArgumentException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    private function getVirtualFilesystemRootPath(): string
    {
        return vfsStream::url($this->rootDirectory) . DIRECTORY_SEPARATOR;
    }
}
