<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View\Items;

/**
 * Class GridTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items\SalesGrid|\PHPUnit_Framework_MockObject_MockObject
     */
    private $salesGridBlock;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items\Grid
     */
    private $grid;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfig;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteItem;

    /**
     * Set up.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $urlBuilder = $this->createMock(
            \Magento\Framework\UrlInterface::class
        );
        $urlBuilder->expects($this->any())->method('getUrl')->willReturn('http://magento.com/catalog/product/edit/1');
        $layout = $this->createMock(
            \Magento\Framework\View\Layout::class
        );
        $block = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['getItems']
        );
        $this->taxConfig = $this->createMock(
            \Magento\Tax\Model\Config::class
        );
        $this->restriction = $this->createMock(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        );
        $this->quoteTotalsFactory = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\Quote\TotalsFactory::class,
            ['create']
        );

        $block->expects($this->any())->method('getItems')->willReturn([$this->quoteItem]);
        $block->setLayout($layout);
        $layout->expects($this->any())->method('getBlock')->willReturn($block);
        $layout->expects($this->any())->method('getParentName')->willReturn('parentName');
        $this->salesGridBlock = $this->createPartialMock(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items\SalesGrid::class,
            ['setQuote', 'setNameInLayout', 'getItems']
        );
        $this->salesGridBlock->expects($this->any())->method('setQuote')->will($this->returnSelf());
        $this->salesGridBlock->expects($this->any())->method('setNameInLayout')->will($this->returnSelf());

        $request = $this->createMock(
            \Magento\Framework\App\Request\Http::class
        );
        $request->expects($this->any())->method('getParam')->willReturn(1);

        $baseCurrencyCode = 'USD';
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $currency->expects($this->any())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);

        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );
        $quote->expects($this->any())->method('getCurrency')->willReturn($currency);

        $this->quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods([
                'getBaseRowTotal',
                'getBaseTaxAmount',
                'getBaseDiscountAmount',
                'getProduct',
                'getId',
                'getTaxAmount'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->negotiableQuoteHelper = $this->createPartialMock(
            \Magento\NegotiableQuote\Helper\Quote::class,
            [
                'resolveCurrentQuote',
                'getFormattedCatalogPrice',
                'getFormattedOriginalPrice',
                'getFormattedCartPrice'
            ]
        );
        $this->negotiableQuoteHelper->expects($this->any())->method('resolveCurrentQuote')->willReturn($quote);

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->setMethods(['format'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->grid = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items\Grid::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'salesGridBlock' => $this->salesGridBlock,
                'data' => [],
                '_urlBuilder' => $urlBuilder,
                'restriction' => $this->restriction,
                'taxConfig' => $this->taxConfig,
                'quoteTotalsFactory' => $this->quoteTotalsFactory,
                'priceCurrency' => $this->priceCurrency
            ]
        );
    }

    /**
     * Test getQuote() method.
     *
     * @return void
     */
    public function testGetQuote()
    {
        $this->assertNotNull($this->grid->getQuote());
    }

    /**
     * Test getProductUrlByItem() method.
     *
     * @return void
     */
    public function testGetProductUrlByItem()
    {
        $this->quoteItem->expects($this->any())->method('getProduct')->willReturnSelf();
        $this->quoteItem->expects($this->any())->method('getId')->willReturn(1);
        $this->assertEquals(
            'http://magento.com/catalog/product/edit/1',
            $this->grid->getProductUrlByItem($this->quoteItem)
        );
    }

    /**
     * Test getItems() method.
     *
     * @return void
     */
    public function testGetItems()
    {
        $this->salesGridBlock->expects($this->any())->method('getItems')->will($this->returnValue([$this->quoteItem]));
        $this->assertEquals([$this->quoteItem], $this->grid->getItems());
    }

    /**
     * Test getFormattedCatalogPrice() method.
     *
     * @return void
     */
    public function testGetFormattedCatalogPrice()
    {
        $formattedPrice = 2354.3;
        $this->negotiableQuoteHelper->expects($this->exactly(1))
            ->method('getFormattedCatalogPrice')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getFormattedCatalogPrice($this->quoteItem));
    }

    /**
     * Test getFormattedOriginalPrice() method.
     *
     * @return void
     */
    public function testGetFormattedOriginalPrice()
    {
        $formattedPrice = 2354.3;
        $this->negotiableQuoteHelper->expects($this->exactly(1))
            ->method('getFormattedOriginalPrice')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getFormattedOriginalPrice($this->quoteItem));
    }

    /**
     * Test getFormattedCartPrice() method.
     *
     * @return void
     */
    public function testGetFormattedCartPrice()
    {
        $formattedPrice = 2354.3;
        $this->negotiableQuoteHelper->expects($this->exactly(1))
            ->method('getFormattedCartPrice')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getFormattedCartPrice($this->quoteItem));
    }

    /**
     * Test getFormattedSubtotal() method.
     *
     * @return void
     */
    public function testGetFormattedSubtotal()
    {
        $baseRowTotal = 23.4;
        $this->quoteItem->expects($this->exactly(1))->method('getBaseRowTotal')->willReturn($baseRowTotal);
        $baseDiscountAmount = 10.3;
        $this->quoteItem->expects($this->exactly(1))->method('getBaseDiscountAmount')->willReturn($baseDiscountAmount);

        $formattedPrice = '12.3';
        $this->priceCurrency->expects($this->exactly(1))->method('format')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getFormattedSubtotal($this->quoteItem));
    }

    /**
     * Test getFormattedCost() method.
     *
     * @return void
     */
    public function testGetFormattedCost()
    {
        $totals = $this->createMock(
            \Magento\NegotiableQuote\Model\Quote\Totals::class
        );
        $this->quoteTotalsFactory->method('create')->willReturn($totals);
        $totals->expects($this->any())->method('getItemCost')->willReturn(20.20);

        $formattedPrice = '15.3';
        $this->priceCurrency->expects($this->exactly(1))->method('format')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getFormattedCost($this->quoteItem));
    }

    /**
     * Test canEdit() method.
     *
     * @return void
     *
     * @param bool $canSubmit
     * @param bool $expectedResult
     * @dataProvider canEditDataProvider
     */
    public function testCanEdit($canSubmit, $expectedResult)
    {
        $this->restriction->expects($this->any())->method('canSubmit')->willReturn($canSubmit);
        $this->assertEquals($expectedResult, $this->grid->canEdit());
    }

    /**
     * Data provider canEdit() for method.
     *
     * @return array
     */
    public function canEditDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * Test getSubtotalTaxLabel method.
     *
     * @return void
     *
     * @param string $firstCall
     * @param bool $displaySalesSubtotalInclTax
     * @param string $secondCall
     * @param bool $displaySalesSubtotalBoth
     * @param string $expectedResult
     * @dataProvider getSubtotalTaxLabelDataProvider
     */
    public function testGetSubtotalTaxLabel(
        $firstCall,
        $displaySalesSubtotalInclTax,
        $secondCall,
        $displaySalesSubtotalBoth,
        $expectedResult
    ) {
        $this->taxConfig->expects($this->$firstCall())
            ->method('displaySalesSubtotalInclTax')
            ->willReturn($displaySalesSubtotalInclTax);
        $this->taxConfig->expects($this->$secondCall())
            ->method('displaySalesSubtotalBoth')
            ->willReturn($displaySalesSubtotalBoth);
        $this->assertEquals($expectedResult, $this->grid->getSubtotalTaxLabel());
    }

    /**
     * Data provider for getSubtotalTaxLabel() method.
     *
     * @return array
     */
    public function getSubtotalTaxLabelDataProvider()
    {
        return [
            ['once', true, 'never', false, 'Subtotal (Incl. Tax)'],
            ['once', false, 'once', true, 'Subtotal (Incl. Tax)'],
            ['once', false, 'once', false, 'Subtotal (Excl. Tax)']
        ];
    }

    /**
     * Test getItemTaxAmount method.
     *
     * @return void
     */
    public function testGetItemTaxAmount()
    {
        $this->quoteItem->expects($this->any())->method('getTaxAmount')->willReturn(20.20);

        $formattedPrice = '19.3';
        $this->priceCurrency->expects($this->exactly(1))->method('format')->willReturn($formattedPrice);

        $this->assertEquals($formattedPrice, $this->grid->getItemTaxAmount($this->quoteItem));
    }

    /**
     * Test getItemSubtotalTaxValue method.
     *
     * @return void
     *
     * @param string $firstCall
     * @param bool $displaySalesSubtotalInclTax
     * @param string $secondCall
     * @param bool $displaySalesSubtotalBoth
     * @param string $thirdCall
     * @param float $expectedResult
     * @dataProvider getItemSubtotalTaxValueDataProvider
     */
    public function testGetItemSubtotalTaxValue(
        $firstCall,
        $displaySalesSubtotalInclTax,
        $secondCall,
        $displaySalesSubtotalBoth,
        $thirdCall,
        $expectedResult
    ) {
        $this->taxConfig->expects($this->$firstCall())
            ->method('displaySalesSubtotalInclTax')
            ->willReturn($displaySalesSubtotalInclTax);
        $this->taxConfig->expects($this->$secondCall())
            ->method('displaySalesSubtotalBoth')
            ->willReturn($displaySalesSubtotalBoth);
        $this->quoteItem->expects($this->any())->method('getBaseRowTotal')->willReturn(80.60);
        $this->quoteItem->expects($this->$thirdCall())->method('getBaseTaxAmount')->willReturn(8.060);
        $this->quoteItem->expects($this->any())->method('getBaseDiscountAmount')->willReturn(10);

        $this->priceCurrency->expects($this->exactly(1))->method('format')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->grid->getItemSubtotalTaxValue($this->quoteItem));
    }

    /**
     * Data provider for getItemSubtotalTaxValue() method.
     *
     * @return array
     */
    public function getItemSubtotalTaxValueDataProvider()
    {
        return [
            ['once', true, 'never', false, 'once', 78.66],
            ['once', false, 'once', true, 'once', 78.66],
            ['once', false, 'once', false, 'never', 70.60]
        ];
    }
}
