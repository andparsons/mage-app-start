<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View;

/**
 * Test for Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Sku class.
 */
class SkuTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Sku
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Sku::class,
            [
                '_layout' => $this->layout,
            ]
        );
    }

    /**
     * Test getHeaderText method.
     *
     * @return void
     */
    public function testGetHeaderText()
    {
        $this->assertEquals(__('Add to Quote by SKU'), $this->block->getHeaderText());
    }

    /**
     * Test getButtonsHtml method.
     *
     * @return void
     */
    public function testGetButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add to Quote'),
            'class' => 'action-default action-add action-secondary',
            'disabled' => 'disabled'
        ];
        $addButtonDataAttribute = [
            'mage-init' => '{"Magento_NegotiableQuote/quote/actions/submit-form":{"formId":"sku-form"}}',
            'role' => 'add-to-quote'
        ];
        $cancelButtonDataAttribute = [
            'mage-init' => '{"Magento_NegotiableQuote/js/quote/actions/toggle-show": '
                . '{"toggleBlockId": "order-additional_area",'
                . ' "showBlockId": "show-sku-form"}}'
        ];
        $cancelButtonData = [
            'label' => __('Cancel')
        ];
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'setDataAttribute', 'toHtml'])
            ->getMockForAbstractClass();
        $this->layout->expects($this->atLeastOnce())
            ->method('createBlock')
            ->with(\Magento\Backend\Block\Widget\Button::class)
            ->willReturn($block);
        $block->expects($this->atLeastOnce())
            ->method('setData')
            ->withConsecutive([$addButtonData], [$cancelButtonData])
            ->willReturnSelf();
        $block->expects($this->atLeastOnce())
            ->method('setDataAttribute')
            ->withConsecutive([$addButtonDataAttribute], [$cancelButtonDataAttribute])
            ->willReturnSelf();
        $block->expects($this->atLeastOnce())
            ->method('toHtml')
            ->willReturnOnConsecutiveCalls('block_html_', 'another_block_html');

        $this->assertEquals('block_html_another_block_html', $this->block->getButtonsHtml());
    }
}
