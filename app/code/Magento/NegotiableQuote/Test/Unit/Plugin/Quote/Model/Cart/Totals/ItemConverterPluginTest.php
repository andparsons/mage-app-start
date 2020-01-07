<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model\Cart\Totals;

/**
 * Class ItemConverterPluginTest.
 */
class ItemConverterPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\Cart\Totals\ItemConverterPlugin
     */
    private $plugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Model\Cart\Totals\ItemConverterPlugin::class
        );
    }

    /**
     * Test for beforeModelToDataObject method.
     *
     * @return void
     */
    public function testBeforeModelToDataObject()
    {
        $itemConverter = $this->createMock(\Magento\Quote\Model\Cart\Totals\ItemConverter::class);
        $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $itemExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartItemExtensionInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getNegotiableQuoteItem']
        );
        $negotiableItem = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class);
        $item->expects($this->exactly(2))->method('getExtensionAttributes')->willReturn($itemExtensionAttributes);
        $itemExtensionAttributes->expects($this->once())->method('getNegotiableQuoteItem')->willReturn($negotiableItem);
        $item->expects($this->once())->method('setData')->with('extension_attributes', null)->willReturnSelf();
        $this->plugin->beforeModelToDataObject($itemConverter, $item);
    }
}
