<?php

namespace Magento\Framework\View\Test\Unit\Layout;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;

/**
 * Class BuilderTest
 * @covers \Magento\Framework\View\Layout\Builder
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    const CLASS_NAME = \Magento\Framework\View\Layout\Builder::class;

    /**
     * @covers \Magento\Framework\View\Layout\Builder::build()
     */
    public function testBuild()
    {
        $fullActionName = 'route_controller_action';

        /** @var Http|\PHPUnit_Framework_MockObject_MockObject */
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->expects($this->exactly(3))->method('getFullActionName')->will($this->returnValue($fullActionName));

        /** @var ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject $processor */
        $processor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $processor->expects($this->once())->method('load');

        /** @var Layout|\PHPUnit_Framework_MockObject_MockObject */
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            $this->getLayoutMockMethods()
        );
        $layout->expects($this->atLeastOnce())->method('getUpdate')->will($this->returnValue($processor));
        $layout->expects($this->atLeastOnce())->method('generateXml')->will($this->returnValue($processor));
        $layout->expects($this->atLeastOnce())->method('generateElements')->will($this->returnValue($processor));

        $data = ['full_action_name' => $fullActionName, 'layout' => $layout];
        /** @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $eventManager->expects($this->at(0))->method('dispatch')->with('layout_load_before', $data);
        $eventManager->expects($this->at(1))->method('dispatch')->with('layout_generate_blocks_before', $data);
        $eventManager->expects($this->at(2))->method('dispatch')->with('layout_generate_blocks_after', $data);
        $builder = $this->getBuilder(['eventManager' => $eventManager, 'request' => $request, 'layout' => $layout]);
        $builder->build();
    }

    /**
     * @return array
     */
    protected function getLayoutMockMethods()
    {
        return ['setBuilder', 'getUpdate', 'generateXml', 'generateElements'];
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\View\Layout\Builder
     */
    protected function getBuilder($arguments)
    {
        return (new ObjectManager($this))->getObject(static::CLASS_NAME, $arguments);
    }
}
