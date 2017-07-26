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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;

/**
 * The composer plugin entry point class.
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    /** @type Composer */
    protected $composer;

    /** @type IOInterface */
    protected $io;

    /**
     * The activation method is called when the plugin is activated.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        /** composer and io properties are assgned for convenience */
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Subscribe this plugin to the wished composer events, with the given callback.
     *
     * @return array The composer events on which this plugin should fire.
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'callback',
            ScriptEvents::POST_UPDATE_CMD  => 'callback'
        );
    }

    /**
     * This callback is called, when the wished composer events are fired.
     */
    public function callback()
    {
        $generator = $this->getGenerator();
        try {
            $this->io->write('<info>Generating OXID eShop unified namespace classes ... </info>', false);
            $generator->cleanupOutputDirectory();
            $generator->generate();
            $this->io->write('<info>Done</info>');
        } catch (\Exception $exception) {
            $this->io->writeError('<error>Failed</error>');
            $this->io->writeError('<error>Error: ' . $exception->getMessage() . '</error>');
            $this->io->writeError('<error>Code: ' . $exception->getCode() . '</error>');
            $this->io->writeError('<error>Stacktrace: ' . PHP_EOL . $exception->getTraceAsString() . '</error>');
        }
    }

    /**
     * Create the Unified Namespace Generator object.
     *
     * @return \OxidEsales\UnifiedNameSpaceGenerator\Generator
     */
    protected function getGenerator()
    {
        $facts = new \OxidEsales\Facts\Facts();
        $unifiedNameSpaceClassMapProvider = new \OxidEsales\UnifiedNameSpaceGenerator\UnifiedNameSpaceClassMapProvider($facts);

        return new \OxidEsales\UnifiedNameSpaceGenerator\Generator($facts, $unifiedNameSpaceClassMapProvider);
    }
}
