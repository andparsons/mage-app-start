<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit tests for QuotePlugin.
 */
class QuotePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Quote\ItemRemove|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemRemove;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddress;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\QuotePlugin
     */
    private $quotePlugin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->itemRemove = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\ItemRemove::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quotePlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Model\QuotePlugin::class,
            [
                'itemRemove' => $this->itemRemove,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * Test for aroundAssignCustomer method.
     *
     * @return void
     */
    public function testAroundAssignCustomer()
    {
        $customerAddressId = 1;
        $snapshot = [
            'shipping_address' => [
                'customer_address_id' => $customerAddressId
            ]
        ];

        $this->prepareQuoteMock();
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getSnapshot')->willReturn($snapshot);
        $billingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAddress->expects($this->exactly(2))->method('getCustomerAddressId')
            ->willReturnOnConsecutiveCalls(null, 1);
        $this->serializer->expects($this->once())->method('unserialize')->with($snapshot)->willReturn($snapshot);
        $this->shippingAddress->expects($this->once())->method('setCustomerAddressId')->with($customerAddressId);
        $this->quote->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($billingAddress);
        $this->quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($this->shippingAddress);
        $closure = function () {
        };
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->quotePlugin->aroundAssignCustomer($this->quote, $closure, $customer);
    }

    /**
     * Test for aroundAssignCustomer method with InvalidArgumentException exception.
     *
     * @return void
     */
    public function testAroundAssignCustomerWithInvalidArgumentException()
    {
        $customerAddressId = 1;
        $snapshot = [
            'shipping_address' => [
                'customer_address_id' => $customerAddressId
            ]
        ];

        $this->prepareQuoteMock();
        $this->negotiableQuote->expects($this->atLeastOnce())->method('getSnapshot')->willReturn($snapshot);
        $billingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAddress->expects($this->exactly(2))->method('getCustomerAddressId')
            ->willReturnOnConsecutiveCalls(null, null);
        $exception = new \InvalidArgumentException(__('exception message'));
        $this->serializer->expects($this->once())->method('unserialize')->with($snapshot)
            ->willThrowException($exception);
        $this->shippingAddress->expects($this->never())->method('setCustomerAddressId');
        $this->quote->expects($this->atLeastOnce())->method('getBillingAddress')->willReturn($billingAddress);
        $this->quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($this->shippingAddress);
        $closure = function () {
        };
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->quotePlugin->aroundAssignCustomer($this->quote, $closure, $customer);
    }

    /**
     * Prepare Quote mock for tests.
     *
     * @return void
     */
    private function prepareQuoteMock()
    {
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $this->negotiableQuote = $this->getMockBuilder(NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSnapshot'])
            ->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);
    }

    /**
     * Test for aroundCollectTotals method.
     *
     * @return void
     */
    public function testAroundCollectTotals()
    {
        $quoteItemId = 1;
        $quoteId = 1;
        $productId = 10;
        $sku = 'sku';
        $expectedSkus = [$sku];

        $this->quote->expects($this->atLeastOnce())->method('getData')->willReturn(true);
        $quote = $this->quote;
        $closure = function () use ($quote) {
            return $quote;
        };
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'isDeleted', 'getItemId', 'getSku'])
            ->getMock();
        $quoteItem->expects($this->atLeastOnce())->method('getItemId')->willReturn($quoteItemId);
        $quoteItem->expects($this->atLeastOnce())->method('getProductId')->willReturn($productId);
        $quoteItem->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $quoteItem->expects($this->atLeastOnce())->method('isDeleted')->willReturn(true);
        $this->quote->expects($this->atLeastOnce())->method('getItemsCollection')->willReturn([$quoteItem]);
        $this->quote->expects($this->atLeastOnce())->method('getItemById')->with($quoteItemId)->willReturn($quoteItem);
        $this->quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $this->itemRemove->expects($this->atLeastOnce())->method('setNotificationRemove')
            ->with(
                $quoteId,
                $productId,
                $expectedSkus
            );

        $this->quotePlugin->aroundCollectTotals($this->quote, $closure);
    }

    /**
     * Test for aroundCollectTotals method when not negotiable quote used.
     *
     * @return void
     */
    public function testAroundCollectTotalsForNotNegotiableQuote()
    {
        $this->quote->expects($this->atLeastOnce())->method('getData')->willReturn(false);
        $quote = $this->quote;
        $closure = function () use ($quote) {
            return $quote;
        };
        $this->quote->expects($this->never())->method('getAllItems');

        $this->quotePlugin->aroundCollectTotals($this->quote, $closure);
    }
}
