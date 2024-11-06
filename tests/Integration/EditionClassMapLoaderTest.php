<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator\Tests\Integration;

use OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition;
use OxidEsales\EshopCommunity\Internal\Framework\Edition\EditionDirectoriesLocator;
use OxidEsales\UnifiedNameSpaceGenerator\EditionClassMapLoader;
use OxidEsales\UnifiedNameSpaceGenerator\Exceptions\InvalidUnifiedNamespaceClassMapException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

final class EditionClassMapLoaderTest extends TestCase
{
    private readonly string $editionClassMapPath;
    private readonly string $editionClassMapBackup;
    private readonly Filesystem $filesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->editionClassMapPath = $this->getEditionClassMapFilePath();
        $this->editionClassMapBackup = "$this->editionClassMapPath.back";
        $this->filesystem->copy($this->editionClassMapPath, $this->editionClassMapBackup, true);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->filesystem->copy($this->editionClassMapBackup, $this->editionClassMapPath, true);
        $this->filesystem->remove($this->editionClassMapBackup);
    }

    public function testLoadWithMissingFile(): void
    {
        $this->filesystem->remove($this->editionClassMapPath);
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        (new EditionClassMapLoader(Edition::Community))->load();
    }

    public function testLoadWithNonArrayFile(): void
    {
        $this->filesystem->dumpFile($this->editionClassMapPath, '<?php "some-string";');
        $this->expectException(InvalidUnifiedNamespaceClassMapException::class);

        (new EditionClassMapLoader(Edition::Community))->load();
    }

    public function testLoad(): void
    {
        $map = (new EditionClassMapLoader(Edition::Community))->load();

        $this->assertIsArray($map);
    }

    private function getEditionClassMapFilePath(): string
    {
        return Path::join(
            (new EditionDirectoriesLocator())->getEditionSourcePath(Edition::Community),
            'Core',
            'Autoload',
            'UnifiedNameSpaceClassMap.php'
        );
    }
}
