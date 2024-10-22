<?php

/**
 * Test class for \Magento\Backend\Model\Url
 */
namespace Magento\Backend\Test\Unit\Block\Widget\Grid;

class ExtendedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testPrepareLoadedCollection()
    {
        $request = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, ['has']);
        $request->expects($this->any())->method('has')->will($this->returnValue(null));

        $columnSet = $this->createMock(\Magento\Backend\Block\Widget\Grid\ColumnSet::class);
        $layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $layout->expects($this->any())->method('getChildName')->will($this->returnValue('grid.columnSet'));
        $layout->expects($this->any())->method('getBlock')->will($this->returnValue($columnSet));

        $collection = $this->createMock(\Magento\Framework\Data\Collection::class);
        $collection->expects($this->atLeastOnce())->method('isLoaded')->will($this->returnValue(true));
        $collection->expects($this->atLeastOnce())->method('clear');
        $collection->expects($this->atLeastOnce())->method('load');

        /** @var \Magento\Backend\Block\Widget\Grid\Extended $block */
        $block = $this->_objectManager->getObject(
            \Magento\Backend\Block\Widget\Grid\Extended::class,
            ['request' => $request, 'layout' => $layout]
        );

        $block->setCollection($collection);
        $block->getPreparedCollection();
    }
}
