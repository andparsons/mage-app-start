<?php
namespace Magento\Framework\Module\Test\Unit;

use \Magento\Framework\Module\ConflictChecker;

class ConflictCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider checkConflictWhenEnableModuleDataProvider
     * @param array $conflictReturnMap
     * @param array $enabledModules
     * @param string $moduleName
     * @param array $expected
     */
    public function testCheckConflictsWhenEnableModules(
        $conflictReturnMap,
        $enabledModules,
        $moduleName,
        $expected
    ) {
        $moduleListMock = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $packageInfoMock = $this->createMock(\Magento\Framework\Module\PackageInfo::class);
        $moduleListMock->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue($enabledModules));
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->will($this->returnValueMap($conflictReturnMap));
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('0.1'));
        $packageInfoFactoryMock = $this->createMock(\Magento\Framework\Module\PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($packageInfoMock));
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals($expected, $conflictChecker->checkConflictsWhenEnableModules($moduleName));
    }

    /**
     * @return array
     */
    public function checkConflictWhenEnableModuleDataProvider()
    {
        return [
            [
                [['Vendor_A', ['Vendor_B' => '0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be 0.1)']]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '0.1']], ['Vendor_B', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_B', ['Vendor_A' => '0.1']], ['Vendor_A', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_B conflicts with current Vendor_A version 0.1 (version should not be 0.1)']]
            ],
            [
                [['Vendor_B', ['Vendor_A' => '0.1']], ['Vendor_A', []]],
                [],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => []]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                ['Vendor_B' => [], 'Vendor_C' => []]
            ],
            [
                [['Vendor_A', ['Vendor_C' => '0.1']], ['Vendor_B', []], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                [
                    'Vendor_B' => [],
                    'Vendor_C' => ['Vendor_A conflicts with current Vendor_C version 0.1 (version should not be 0.1)']
                ]
            ],
            [
                [['Vendor_A', []], ['Vendor_B', ['Vendor_C' => '0.1']], ['Vendor_C', []]],
                ['Vendor_A'],
                ['Vendor_B', 'Vendor_C'],
                [
                    'Vendor_B' => ['Vendor_B conflicts with current Vendor_C version 0.1 (version should not be 0.1)'],
                    'Vendor_C' => ['Vendor_B conflicts with current Vendor_C version 0.1 (version should not be 0.1)']
                ]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '>=0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be >=0.1)']]
            ],
            [
                [['Vendor_A', ['Vendor_B' => '~0.1']], ['Vendor_B', []]],
                ['Vendor_A'],
                ['Vendor_B'],
                ['Vendor_B' => ['Vendor_A conflicts with current Vendor_B version 0.1 (version should not be ~0.1)']]
            ],
        ];
    }

    public function testCheckConflictWhenEnableModuleDifferentVersion()
    {
        $moduleListMock = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $packageInfoMock = $this->createMock(\Magento\Framework\Module\PackageInfo::class);
        $moduleListMock->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Vendor_A', 'Vendor_B']));
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->will($this->returnValueMap([
                ['Vendor_A', []],
                ['Vendor_B', []],
                ['Vendor_C', ['Vendor_A' => '>=0.2,<0.3', 'Vendor_B' => '<0.4']]
            ]));
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValueMap([['Vendor_A', '0.2'], ['Vendor_B', '0.4']]));
        $packageInfoFactoryMock = $this->createMock(\Magento\Framework\Module\PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($packageInfoMock));
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals(
            ['Vendor_C' => ['Vendor_C conflicts with current Vendor_A version 0.2 (version should not be >=0.2,<0.3)']],
            $conflictChecker->checkConflictsWhenEnableModules(['Vendor_C'])
        );
    }

    public function testCheckConflictWhenEnableModuleDifferentVersionWithCurEnabledModules()
    {
        $moduleListMock = $this->createMock(\Magento\Framework\Module\ModuleList::class);
        $packageInfoMock = $this->createMock(\Magento\Framework\Module\PackageInfo::class);
        $packageInfoMock->expects($this->any())
            ->method('getConflict')
            ->will($this->returnValueMap([
                ['Vendor_A', []],
                ['Vendor_B', []],
                ['Vendor_C', ['Vendor_A' => '>=0.2,<0.3', 'Vendor_B' => '<0.4']]
            ]));
        $packageInfoMock->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValueMap([['Vendor_A', '0.2'], ['Vendor_B', '0.4']]));
        $packageInfoFactoryMock = $this->createMock(\Magento\Framework\Module\PackageInfoFactory::class);
        $packageInfoFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($packageInfoMock));
        $conflictChecker = new ConflictChecker($moduleListMock, $packageInfoFactoryMock);
        $this->assertEquals(
            ['Vendor_C' => ['Vendor_C conflicts with current Vendor_A version 0.2 (version should not be >=0.2,<0.3)']],
            $conflictChecker->checkConflictsWhenEnableModules(['Vendor_C'], ['Vendor_A', 'Vendor_B'])
        );
    }
}
