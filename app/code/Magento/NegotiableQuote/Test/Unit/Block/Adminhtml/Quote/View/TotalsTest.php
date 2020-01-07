<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View;

/**
 * Class TotalsTest.
 */
class TotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totals;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->priceCurrencyMock = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );

        $this->quoteTotalsFactory = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Quote\TotalsFactory::class,
            ['create']
        );

        $this->negotiableQuoteHelper = $this->createMock(
            \Magento\NegotiableQuote\Helper\Quote::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->totals = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper
            ]
        );
    }

    /**
     * Set Up quote Mock.
     *
     * @return void
     */
    private function setUpQuoteMock()
    {
        $baseCurrencyCode = 'USD';
        $quoteCurrencyCode = 'EUR';
        $quoteCurrency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods([
                'getBaseCurrencyCode',
                'getQuoteCurrencyCode'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quoteCurrency->expects($this->any())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $quoteCurrency->expects($this->any())->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);

        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getExtensionAttributes',
                'getCurrency'
            ])
            ->getMockForAbstractClass();
        $this->quote->expects($this->any())->method('getCurrency')->willReturn($quoteCurrency);

        $this->negotiableQuoteHelper->expects($this->atLeastOnce())
            ->method('resolveCurrentQuote')->willReturn($this->quote);
    }

    /**
     * Test displayPrices() method.
     *
     * @return void
     */
    public function testDisplayPrices()
    {
        $this->setUpQuoteMock();

        $price = 5.5;
        $this->priceCurrencyMock->expects($this->once())->method('format')->willReturn($price);

        $this->assertEquals(5.5, $this->totals->displayPrices($price));
    }

    /**
     * Test getTotals() method.
     *
     * @return void
     */
    public function testGetTotals()
    {
        $this->setUpQuoteMock();

        $quoteTotals = $this->createMock(
            \Magento\NegotiableQuote\Model\Quote\Totals::class
        );
        $this->quoteTotalsFactory->expects($this->once())->method('create')->willReturn($quoteTotals);

        $quoteTotals->expects($this->once())->method('getTotalCost');
        $quoteTotals->expects($this->once())->method('getQuoteShippingPrice');

        $negotiableQuote = $this->createMock(
            \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class
        );
        $negotiableQuote->expects($this->atLeastOnce())->method('getNegotiatedPriceType');
        $negotiableQuote->expects($this->atLeastOnce())->method('getNegotiatedPriceValue');

        $quoteExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $quoteExtension->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuote);

        $this->quote->expects($this->any())->method('getExtensionAttributes')->willReturn($quoteExtension);

        $this->assertInternalType('array', $this->totals->getTotals());
    }
}
