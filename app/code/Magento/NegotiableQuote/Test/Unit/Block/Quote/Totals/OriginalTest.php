<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote\Totals;

/**
 * Class OriginalTest.
 */
class OriginalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataHelper;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Totals\Original
     */
    private $original;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->postDataHelper = $this->createMock(\Magento\Framework\Data\Helper\PostHelper::class);
        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->priceCurrency = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->original = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Totals\Original::class,
            [
                'postDataHelper' => $this->postDataHelper,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'priceCurrency' => $this->priceCurrency,
                '_layout' => $this->layout,
                'data' => [],
            ]
        );
    }

    /**
     * Test displayPrices.
     *
     * @return void
     */
    public function testDisplayPrices()
    {
        $parentName = 'parentName';
        $block = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\BlockInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getTotals']
        );
        $block->expects($this->once())->method('getTotals')->willReturn(['catalog_price' => 10.00]);
        $this->layout->expects($this->once())->method('getParentName')->willReturn($parentName);
        $this->layout->expects($this->once())->method('getBlock')->with($parentName)->willReturn($block);
        $price = '$10.00';
        $this->priceCurrency->expects($this->once())->method('format')->willReturn($price);

        $this->assertEquals($price, $this->original->displayPrices(10.00, 'USD'));
    }

    /**
     * Test formatPrice.
     *
     * @return void
     */
    public function testFormatPrice()
    {
        $price = '$10.00';
        $this->negotiableQuoteHelper->expects($this->once())->method('formatPrice')->willReturn($price);

        $this->assertEquals($price, $this->original->formatPrice(10.00, 'USD'));
    }

    /**
     * Test getCurrencySymbol.
     *
     * @return void
     */
    public function testGetCurrencySymbol()
    {
        $currency = $this->createMock(\Magento\Quote\Api\Data\CurrencyInterface::class);
        $currency->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $quote = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->expects($this->once())->method('getCurrency')->willReturn($currency);
        $this->negotiableQuoteHelper->expects($this->once())->method('resolveCurrentQuote')->willReturn($quote);
        $currencySymbol = '$';
        $this->priceCurrency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);

        $this->assertEquals($currencySymbol, $this->original->getCurrencySymbol());
    }
}
