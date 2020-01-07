<?php
namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Test for Magento\NegotiableQuote\Model\PriceCurrency class.
 */
class PriceCurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyObject;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currencyFactory;

    /**
     * @var \Magento\NegotiableQuote\Model\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->priceCurrencyObject = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->currencyFactory = $this->getMockBuilder(\Magento\Directory\Model\CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->priceCurrency = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\PriceCurrency::class,
            [
                'priceCurrency' => $this->priceCurrencyObject,
                'currencyFactory' => $this->currencyFactory,
            ]
        );
    }

    /**
     * Test method getCurrency where currency set as string.
     *
     * @return void
     */
    public function testGetCurrencyWithString()
    {
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyFactory->expects($this->once())->method('create')->willReturn($currency);
        $currency->expects($this->once())->method('load')->with('USD')->willReturnSelf();
        $this->priceCurrencyObject->expects($this->never())->method('getCurrency');

        $this->assertEquals($currency, $this->priceCurrency->getCurrency(null, 'USD'));
    }

    /**
     * Test method getCurrency where currency set as null.
     *
     * @return void
     */
    public function testGetCurrencyWithEmpty()
    {
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyFactory->expects($this->never())->method('create');
        $currency->expects($this->never())->method('load');
        $this->priceCurrencyObject->expects($this->once())->method('getCurrency')->willReturn($currency);

        $this->assertEquals($currency, $this->priceCurrency->getCurrency(null, null));
    }
}
