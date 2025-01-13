<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\UnifiedNameSpaceGenerator;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

readonly class Plugin implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;
    private Composer $composer;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->io = $io;
        $this->composer = $composer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'callback',
            ScriptEvents::POST_UPDATE_CMD  => 'callback'
        ];
    }

    public function callback(): void
    {
        $this->requireAutoload();

        $this->io->write('<info>Generating OXID eShop unified namespace classes</>');

        $generator = new Generator(
            new UnifiedNameSpaceClassMapProvider()
        );
        $generator->cleanupOutputDirectory();
        $generator->generate();
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    private function requireAutoload(): void
    {
        require_once $this->composer->getConfig()->get('vendor-dir') . '/autoload.php';
    }
}
