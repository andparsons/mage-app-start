<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Quote\Model;

/**
 * Test for Magento\NegotiableQuote\Plugin\Quote\Model\UpdateShippingMethodPlugin class.
 */
class UpdateShippingMethodPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodManagement;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Quote\Model\UpdateShippingMethodPlugin
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->shippingMethodManagement = $this
            ->getMockBuilder(\Magento\Quote\Api\ShippingMethodManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Quote\Model\UpdateShippingMethodPlugin::class,
            [
                'shippingMethodManagement' => $this->shippingMethodManagement
            ]
        );
    }

    /**
     * Test afterLoad method.
     *
     * @return void
     */
    public function testAfterLoad()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository\LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getId', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'getShippingAssignments'])
            ->getMockForAbstractClass();
        $shippingAssignment = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getShippingAssignments')
            ->willReturn([$shippingAssignment]);
        $shipping = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $shippingAssignment->expects($this->atLeastOnce())->method('getShipping')->willReturn($shipping);
        $shipping->expects($this->atLeastOnce())->method('getMethod')->willReturn('free_free');
        $shipping->expects($this->once())->method('setMethod');
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $negotiableQuote->expects($this->once())->method('setShippingPrice');
        $shippingMethod = $this->getMockBuilder(\Magento\Quote\Api\Data\ShippingMethodInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $shippingMethod->expects($this->atLeastOnce())->method('getCarrierCode')->willReturn('fix');
        $shippingMethod->expects($this->atLeastOnce())->method('getMethodCode')->willReturn('fix');
        $this->shippingMethodManagement->expects($this->once())->method('getList')->willReturn([$shippingMethod]);

        $this->assertEquals($quote, $this->plugin->afterLoad($subject, $quote));
    }

    /**
     * Test afterLoad method with quote in ordered status.
     *
     * @return void
     */
    public function testAfterLoadWithOrderedQuote()
    {
        $quoteId = 1;
        $subject = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository\LoadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getId', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote', 'getShippingAssignments'])
            ->getMockForAbstractClass();
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getNegotiableQuote')
            ->willReturn($negotiableQuote);
        $extensionAttributes->expects($this->never())
            ->method('getShippingAssignments');
        $quote->expects($this->atLeastOnce())->method('getId')->willReturn($quoteId);
        $negotiableQuote->expects($this->atLeastOnce())->method('getIsRegularQuote')->willReturn(true);
        $negotiableQuote->expects($this->once())->method('getStatus')
            ->willReturn(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_ORDERED);

        $this->assertEquals($quote, $this->plugin->afterLoad($subject, $quote));
    }
}
