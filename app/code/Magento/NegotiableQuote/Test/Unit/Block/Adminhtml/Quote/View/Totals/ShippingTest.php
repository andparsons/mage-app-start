<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View\Totals;

/**
 * Class ShippingTest
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals\Shipping
     */
    protected $shipping;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteHelper;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->shipping = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals\Shipping::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
            ]
        );
    }

    /**
     * Test canEdit
     *
     * @return void
     */
    public function testCanEdit()
    {
        $this->negotiableQuoteHelper->expects($this->once())->method('isSubmitAvailable')->willReturn(true);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress'])
            ->getMockForAbstractClass();
        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $address->expects($this->atLeastOnce())->method('getPostcode')->willReturn(11001);
        $quote->expects($this->atLeastOnce())->method('getShippingAddress')->willReturn($address);
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $layout->expects($this->atLeastOnce())->method('getParentName')->will($this->returnValue('parent'));
        $parent = $this->createMock(\Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals::class);
        $parent->expects($this->atLeastOnce())->method('getQuote')->willReturn($quote);
        $layout->expects($this->atLeastOnce())->method('getBlock')->willReturn($parent);

        $this->shipping->setLayout($layout);
        $this->assertTrue($this->shipping->canEdit());
    }
}
