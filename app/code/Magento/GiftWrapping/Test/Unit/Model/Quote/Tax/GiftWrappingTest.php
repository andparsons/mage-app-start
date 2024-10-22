<?php
namespace Magento\GiftWrapping\Test\Unit\Model\Quote\Tax;

use Magento\Quote\Model\Quote\Address;

/**
 * Test class for \Magento\GiftWrapping\Model\Quote\Tax\Giftwrapping
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftWrappingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GiftWrapping\Model\Wrapping
     */
    protected $wrappingMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->wrappingMock = $this->createPartialMock(
            \Magento\GiftWrapping\Model\Wrapping::class,
            ['load', 'setStoreId', 'getBasePrice', '__wakeup']
        );
    }

    /**
     * Test for collect method
     */
    public function testCollectQuote()
    {
        $helperMock = $this->createMock(\Magento\GiftWrapping\Helper\Data::class);
        $helperMock->expects($this->any())->method('getWrappingTaxClass')->will($this->returnValue(2));
        $wrappingFactoryMock = $this->createPartialMock(\Magento\GiftWrapping\Model\WrappingFactory::class, ['create']);
        $wrappingFactoryMock->expects($this->once())->method('create')->willReturn($this->wrappingMock);
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['setGiftWrappingPrice', 'isVirtual', '__wakeup']
        );
        $addressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getAddressType',
                '__wakeup',
                'setGwItemsBaseTaxAmount',
                'setGwItemsTaxAmount',
                'getExtraTaxableDetails',
                'getCustomAttributesCodes'
            ]
        );
        $product->expects($this->any())->method('isVirtual')->willReturn(false);
        $this->wrappingMock->expects($this->any())->method('load')->willReturnSelf();
        $this->wrappingMock->expects($this->any())->method('getBasePrice')->willReturn(6);
        $item = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['setAssociatedTaxables', '__wakeup', 'setProduct', 'getProduct', 'getQty', 'getGwId']
        );
        $product->setGiftWrappingPrice(10);
        $item->expects($this->once())->method('getGwId')->willReturn(1);
        $item->expects($this->any())->method('getProduct')->willReturn($product);
        $item->expects($this->any())->method('getQty')->willReturn(2);
        $addressMock->expects($this->any())->method('getAddressType')->willReturn('shipping');
        $addressMock->expects($this->any())->method('getCustomAttributesCodes')->willReturn([]);
        $expected = [
            [
                'type' => 'item_gw',
                'code' => 'item_gw1',
                'unit_price' => 10,
                'base_unit_price' => 6,
                'quantity' => 2,
                'tax_class_id' => 2,
                'price_includes_tax' => false,
            ],
        ];
        $item->expects($this->once())->method('setAssociatedTaxables')->with($expected);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['convertPrice', 'getId', '__wakeup']);
        $storeMock->expects($this->any())->method('convertPrice')->willReturn(10);

        $quoteData = [
            'isMultishipping' => false,
            'store' => $storeMock,
            'billingAddress' => null,
            'customerTaxClassId' => null,
            'tax_class_id' => 2,
        ];
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->expects($this->any())->method('setData')->with($quoteData)->willReturnSelf();
        $quote->expects($this->once())->method('getStore')->willReturn($storeMock);
        $this->wrappingMock->expects($this->once())->method('setStoreId')->willReturnSelf();
        $this->wrappingMock->expects($this->once())->method('load')->willReturnSelf();
        $priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $helperMock->expects($this->any())->method('getPrintedCardPrice');
        $priceCurrencyMock->expects($this->any())->method('convert')->willReturn(10);
        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingMock->expects($this->atLeastOnce())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->atLeastOnce())->method('getShipping')->willReturn($shippingMock);
        $shippingAssignmentMock->expects($this->once())->method('getItems')->willReturn([$item]);
        $totalMock = $this->createMock(\Magento\Quote\Model\Quote\Address\Total::class);
        $model = new \Magento\GiftWrapping\Model\Total\Quote\Tax\Giftwrapping(
            $helperMock,
            $priceCurrencyMock,
            $wrappingFactoryMock
        );
        $model->collect($quote, $shippingAssignmentMock, $totalMock);
    }
}
