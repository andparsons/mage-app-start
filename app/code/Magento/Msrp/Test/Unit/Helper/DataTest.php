<?php

namespace Magento\Msrp\Test\Unit\Helper;

use Magento\Msrp\Pricing\MsrpPriceCalculatorInterface;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var MsrpPriceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $msrpPriceCalculator;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMsrp', 'getPriceInfo', '__wakeup'])
            ->getMock();
        $this->msrpPriceCalculator = $this->getMockBuilder(MsrpPriceCalculatorInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->helper = $objectManager->getObject(
            \Magento\Msrp\Helper\Data::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'msrpPriceCalculator' => $this->msrpPriceCalculator,
            ]
        );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testIsMinimalPriceLessMsrp()
    {
        $msrp = 120;
        $convertedFinalPrice = 200;
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->will(
                $this->returnCallback(
                    function ($arg) {
                        return round(2 * $arg, 2);
                    }
                )
            );

        $finalPriceMock = $this->getMockBuilder(\Magento\Catalog\Pricing\Price\FinalPrice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $finalPriceMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($convertedFinalPrice));

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
            ->will($this->returnValue($finalPriceMock));

        $this->msrpPriceCalculator
            ->expects($this->any())
            ->method('getMsrpPriceValue')
            ->willReturn($msrp);
        $this->productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $result = $this->helper->isMinimalPriceLessMsrp($this->productMock);
        $this->assertTrue($result, "isMinimalPriceLessMsrp returned incorrect value");
    }
}
