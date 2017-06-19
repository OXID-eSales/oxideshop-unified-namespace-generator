<?php
/**
 * This file is part of one OXID eShop Composer plugin.
 *
 * OXID eShop Composer plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Composer plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Composer plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\UnifiedNameSpaceGenerator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Script\ScriptEvents;

/**
 * The composer plugin entry point class.
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * The activation method is called when the plugin is activated.
     *
     * It is empty, cause we don't want to do anything at this point of time, but
     * we have to implement it.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {

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
            ScriptEvents::POST_UPDATE_CMD => 'callback'
        );
    }

    /**
     * This callback is called, when the wished composer events are fired.
     */
    public function callback()
    {
        $generator = $this->getGenerator();

        $generator->cleanupOutputDirectory();
        $generator->generate();
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
