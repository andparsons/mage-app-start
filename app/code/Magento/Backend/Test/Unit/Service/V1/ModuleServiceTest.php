<?php
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Service\V1;

use Magento\Backend\Service\V1\ModuleService;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Module List Service Test
 *
 * Covers \Magento\Sales\Model\ValidatorResultMerger
 */
class ModuleServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var ModuleService
     */
    private $moduleService;

    /**
     * @var ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->moduleListMock = $this->createMock(ModuleListInterface::class);
        $this->objectManager = new ObjectManager($this);
        $this->moduleService = $this->objectManager->getObject(
            ModuleService::class,
            [
                'moduleList' => $this->moduleListMock,
            ]
        );
    }

    /**
     * Test getModules method
     *
     * @return void
     */
    public function testGetModules()
    {
        $moduleNames = ['Magento_Backend', 'Magento_Catalog', 'Magento_Customer'];
        $this->moduleListMock->expects($this->once())->method('getNames')->willReturn($moduleNames);

        $expected = $moduleNames;
        $actual = $this->moduleService->getModules();
        $this->assertEquals($expected, $actual);
    }
}
