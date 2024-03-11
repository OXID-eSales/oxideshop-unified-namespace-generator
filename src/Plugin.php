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
use http\Exception\BadMethodCallException;
use OxidEsales\Facts\Facts;

/**
 * The composer plugin entry point class.
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected Composer $composer;
    protected IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'callback',
            ScriptEvents::POST_UPDATE_CMD  => 'callback'
        );
    }

    public function callback(): void
    {
        $generator = $this->getGenerator();
        try {
            $this->io->write(
                '<info>Generating OXID eShop unified namespace classes ... </info>',
                false
            );
            $generator->cleanupOutputDirectory();
            $generator->generate();
            $this->io->write('<info>Done</info>');
        } catch (\Exception $exception) {
            $this->io->writeError('<error>Failed</error>');
            $this->io->writeError('<error>Error: ' . $exception->getMessage() . '</error>');
            $this->io->writeError('<error>Code: ' . $exception->getCode() . '</error>');
            $this->io->writeError(
                '<error>Stacktrace: ' . PHP_EOL . $exception->getTraceAsString() . '</error>'
            );
        }
    }

    protected function getGenerator(): Generator
    {
        $facts = new Facts();
        $unifiedNameSpaceClassMapProvider = new UnifiedNameSpaceClassMapProvider($facts);

        return new Generator($facts, $unifiedNameSpaceClassMapProvider);
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }
}
