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

namespace OxidEsales\UnifiedNameSpaceGenerator\tests\Integration;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\Package;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableArrayRepository;
use OxidEsales\UnifiedNameSpaceGenerator\Plugin;

/**
 * Class PluginTest
 *
 * @package OxidEsales\UnifiedNameSpaceGenerator\tests
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \OxidEsales\UnifiedNameSpaceGenerator\Plugin::getExtraMap
     */
    public function testGetExtraMap()
    {
        $plugin = new Plugin;
        $plugin->activate($composer = new Composer(), new NullIO);
        $composer->setRepositoryManager($manager = new RepositoryManager(new NullIO, new Config));
        $manager->setLocalRepository($repo = new WritableArrayRepository());
        $repo->addPackage($p1 = new Package(null, null, null));
        $repo->addPackage(new Package(null, null, null));
        $repo->addPackage($p2 = new Package(null, null, null));

        $p1->setExtra(['oxideshop' => ['unified-namespace-map' => [
            'u1' => 'p1e1',
            'u2' => 'p1e2',
        ]]]);
        $p2->setExtra(['oxideshop' => ['unified-namespace-map' => [
            'u3' => 'p2e1',
            'u2' => 'p2e2',
        ]]]);

        $this->markTestIncomplete();
        // @todo $plugin->callback();
    }
}
