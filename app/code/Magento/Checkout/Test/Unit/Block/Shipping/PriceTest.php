<?php
namespace Magento\Checkout\Test\Unit\Block\Shipping;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    const SUBTOTAL = 10;

    /**
     * @var \Magento\Checkout\Block\Shipping\Price
     */
    protected $priceObj;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();

        $this->priceObj = $objectManager->getObject(
            \Magento\Checkout\Block\Shipping\Price::class,
            ['priceCurrency'   => $this->priceCurrency]
        );
    }

    public function testGetShippingPrice()
    {
        $shippingPrice = 5;
        $convertedPrice = "$5";

        $shippingRateMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', '__wakeup'])
            ->getMock();
        $shippingRateMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($shippingPrice));

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($shippingPrice, true, true)
            ->willReturn($convertedPrice);

        $this->priceObj->setShippingRate($shippingRateMock);
        $this->assertEquals($convertedPrice, $this->priceObj->getShippingPrice());
    }
}
