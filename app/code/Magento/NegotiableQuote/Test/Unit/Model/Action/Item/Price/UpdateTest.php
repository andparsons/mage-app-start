<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Action\Item\Price;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Unit test for Update model.
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\FormatInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\NegotiableQuote\Model\Action\Item\Price\Update
     */
    private $quoteItemPriceUpdater;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->localeFormat = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteItemPriceUpdater = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Action\Item\Price\Update::class,
            [
                'localeFormat' => $this->localeFormat,
                'serializer' => $this->serializer,
            ]
        );
    }

    /**
     * Test for update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $customPrice = 15;
        $buyRequestData = ['buy_request_data'];
        $item = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->setMethods(
                [
                    'getBuyRequest',
                    'getProduct',
                    'addOption',
                    'setCustomPrice',
                    'setOriginalCustomPrice',
                    'setNoDiscount'
                ]
            )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->localeFormat->expects($this->once())->method('getNumber')->with($customPrice)->willReturnArgument(0);
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['setCustomPrice', 'setValue', 'setCode', 'setProduct', 'getData'])
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getBuyRequest')->willReturn($buyRequest);
        $buyRequest->expects($this->once())->method('setCustomPrice')->with($customPrice)->willReturnSelf();
        $buyRequest->expects($this->once())->method('getData')->willReturn($buyRequestData);
        $this->serializer->expects($this->once())
            ->method('serialize')->with($buyRequestData)->willReturn(json_encode($buyRequestData));
        $buyRequest->expects($this->once())->method('setValue')->with(json_encode($buyRequestData))->willReturnSelf();
        $buyRequest->expects($this->once())->method('setCode')->with('info_buyRequest')->willReturnSelf();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getProduct')->willReturn($product);
        $buyRequest->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $item->expects($this->once())->method('addOption')->with($buyRequest)->willReturnSelf();
        $item->expects($this->once())->method('setCustomPrice')->with($customPrice)->willReturnSelf();
        $item->expects($this->once())->method('setOriginalCustomPrice')->with($customPrice)->willReturnSelf();
        $item->expects($this->once())->method('setNoDiscount')->with(true)->willReturnSelf();
        $this->quoteItemPriceUpdater->update($item, ['custom_price' => $customPrice]);
    }

    /**
     * Test for update method with empty custom price.
     *
     * @return void
     */
    public function testUpdateWithEmptyCustomPrice()
    {
        $customPrice = null;
        $buyRequestData = ['buy_request_data'];
        $item = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->setMethods(
                [
                    'hasData',
                    'getBuyRequest',
                    'getProduct',
                    'addOption',
                    'unsetData',
                    'setNoDiscount'
                ]
            )->disableOriginalConstructor()->getMockForAbstractClass();
        $item->expects($this->once())->method('hasData')->with('custom_price')->willReturn(true);
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['unsetData', 'setValue', 'setCode', 'setProduct', 'getData'])
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getBuyRequest')->willReturn($buyRequest);
        $buyRequest->expects($this->once())->method('unsetData')->with('custom_price')->willReturnSelf();
        $buyRequest->expects($this->once())->method('getData')->willReturn($buyRequestData);
        $this->serializer->expects($this->once())
            ->method('serialize')->with($buyRequestData)->willReturn(json_encode($buyRequestData));
        $buyRequest->expects($this->once())->method('setValue')->with(json_encode($buyRequestData))->willReturnSelf();
        $buyRequest->expects($this->once())->method('setCode')->with('info_buyRequest')->willReturnSelf();
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $item->expects($this->once())->method('getProduct')->willReturn($product);
        $buyRequest->expects($this->once())->method('setProduct')->with($product)->willReturnSelf();
        $item->expects($this->once())->method('addOption')->with($buyRequest)->willReturnSelf();
        $item->expects($this->atLeastOnce())
            ->method('unsetData')->withConsecutive(['custom_price'], ['original_custom_price'])->willReturnSelf();
        $item->expects($this->once())->method('setNoDiscount')->with(false)->willReturnSelf();
        $this->quoteItemPriceUpdater->update($item, ['custom_price' => $customPrice, 'use_discount' => true]);
    }
}
