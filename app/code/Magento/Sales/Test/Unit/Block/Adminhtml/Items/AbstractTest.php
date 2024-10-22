<?php
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetItemRenderer()
    {
        $renderer = $this->createMock(\Magento\Framework\View\Element\AbstractBlock::class);
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', 'getGroupChildNames', '__wakeup']
        );
        $layout->expects(
            $this->at(0)
        )->method(
            'getChildName'
        )->with(
            null,
            'some-type'
        )->will(
            $this->returnValue('some-block-name')
        );
        $layout->expects(
            $this->at(1)
        )->method(
            'getBlock'
        )->with(
            'some-block-name'
        )->will(
            $this->returnValue($renderer)
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Items\AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Renderer for type "some-type" does not exist.
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer()
    {
        $renderer = $this->createMock(\stdClass::class);
        $layout = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['getChildName', 'getBlock', '__wakeup']
        );
        $layout->expects(
            $this->at(0)
        )->method(
            'getChildName'
        )->with(
            null,
            'some-type'
        )->will(
            $this->returnValue('some-block-name')
        );
        $layout->expects(
            $this->at(1)
        )->method(
            'getBlock'
        )->with(
            'some-block-name'
        )->will(
            $this->returnValue($renderer)
        );

        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $block = $this->_objectManager->getObject(
            \Magento\Sales\Block\Adminhtml\Items\AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    \Magento\Backend\Block\Template\Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }
}
