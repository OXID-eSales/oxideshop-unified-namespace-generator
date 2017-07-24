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
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2017
 */

namespace OxidEsales\UnifiedNameSpaceGenerator\tests;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test, that the methods of the generator we want are called.
     */
    public function testGeneratorMethodsAreCalled()
    {
        $generatorMock = $this->getMockBuilder(\OxidEsales\UnifiedNameSpaceGenerator\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['cleanupOutputDirectory', 'generate'])
            ->getMock();
        $generatorMock->expects($this->once())->method('cleanupOutputDirectory');
        $generatorMock->expects($this->once())->method('generate');

        $pluginMock = $this->getMock(\OxidEsales\UnifiedNameSpaceGenerator\Plugin::class, ['getGenerator']);
        $pluginMock->expects($this->atLeastOnce())->method('getGenerator')->willReturn($generatorMock);
        $pluginMock->callback();
    }
}
