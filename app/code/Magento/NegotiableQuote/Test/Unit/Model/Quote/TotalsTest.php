<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;

/**
 * Unit tests for Negotiable Quote Totals model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TotalsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemModelMock;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteItemMock;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteItemFactoryMock;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Totals
     */
    protected $model;

    /**
     * @var \Magento\Quote\Api\Data\CartItemExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributes;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods([
                'getSubtotal',
                'isVirtual',
                'getAllVisibleItems',
                'getShippingAddress',
                'getGrandTotal',
                'getBaseGrandTotal',
                'getSubtotalWithDiscount',
                'getBaseSubtotalWithDiscount',
                'getExtensionAttributes'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->taxConfigMock = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->setMethods([
                'displaySalesTaxWithGrandTotal',
                'displaySalesSubtotalInclTax',
                'displaySalesSubtotalBoth',
                'shippingPriceIncludesTax'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->quoteItemModelMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods(['getExtensionAttributes', 'getQty', 'getProduct', 'getItemId', 'setExtensionAttributes'])
            ->disableOriginalConstructor()->getMock();

        $this->storeManagerMock = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore'])->disableOriginalConstructor()->getMockForAbstractClass();

        $this->negotiableQuoteItemMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteItem::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->negotiableQuoteItemFactoryMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory::class)
            ->setMethods(['create', 'load'])
            ->disableOriginalConstructor()->getMock();

        $this->extensionFactory = $this
            ->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->setMethods(['create', 'setNegotiableQuoteItem'])
            ->disableOriginalConstructor()->getMock();

        $this->negotiableQuoteMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->setMethods(['getShippingPrice', 'getNegotiatedPriceType', 'getNegotiatedPriceValue'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->billingAddressMock = $this
            ->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)
            ->setMethods(['getTaxAmount', 'getBaseTaxAmount'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->setMethods([
                'getTaxAmount',
                'getBaseTaxAmount',
                'getShippingTaxAmount',
                'getBaseShippingTaxAmount',
                'getShippingAmount',
                'getBaseShippingAmount'
            ])
            ->disableOriginalConstructor()->getMock();

        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->setMethods(['getNegotiableQuoteItem', 'getNegotiableQuote','setNegotiableQuoteItem'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Quote\Totals::class,
            [
                'taxConfig' => $this->taxConfigMock,
                'storeManager' => $this->storeManagerMock,
                'quoteRepository' => $this->quoteRepository,
                'quote' => $this->quoteMock,
                'negotiableQuoteItemFactory' => $this->negotiableQuoteItemFactoryMock,
                'extensionFactory' => $this->extensionFactory
            ]
        );
    }

    /**
     * Prepare Quote Currency Mock.
     *
     * @param float $currencyRate
     * @param array $calls
     * @return void
     */
    private function prepareQuoteCurrencyMock($currencyRate, array $calls)
    {
        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods(['getBaseToQuoteRate'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $currency->expects($this->exactly($calls['currencyGetBaseToQuoteRate']))
            ->method('getBaseToQuoteRate')->willReturn($currencyRate);
        $this->quoteMock->expects($this->exactly($calls['quoteGetCurrency']))
            ->method('getCurrency')->willReturn($currency);
    }

    /**
     * Test getCatalogTotalPriceWithoutTax() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $currencyRate
     * @param float $price
     * @param float $qty
     * @param float $expected
     * @param array $calls
     * @dataProvider dataProviderGetCatalogTotalPriceWithoutTax
     * @return void
     */
    public function testGetCatalogTotalPriceWithoutTax(
        $inQuoteCurrency,
        $currencyRate,
        $price,
        $qty,
        $expected,
        array $calls
    ) {
        $this->prepareQuoteItems($qty);

        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price);
        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 1);

        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expected, $this->model->getCatalogTotalPriceWithoutTax($inQuoteCurrency));
    }

    /**
     * Data Provider for getCatalogTotalPriceWithoutTax() method.
     *
     * @return array
     */
    public function dataProviderGetCatalogTotalPriceWithoutTax()
    {
        return [
            [
                false, null, 200, 3, 600,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                false, null, 10, 5, 50,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                true, 1.3, 200, 3, 780,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                true, 1.5, 10, 5, 75,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ]
        ];
    }

    /**
     * Test getCatalogTotalPriceWithTax() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $price
     * @param float $qty
     * @param float|null $currencyRate
     * @param float $tax
     * @param float $expects
     * @param array $calls
     * @dataProvider dataProviderGetCatalogTotalPriceWithTax
     * @return void
     */
    public function testGetCatalogTotalPriceWithTax(
        $inQuoteCurrency,
        $price,
        $qty,
        $currencyRate,
        $tax,
        $expects,
        array $calls
    ) {
        $this->prepareQuoteItems($qty);

        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 0);
        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 1);
        $this->getOriginalTaxValue($tax, 2);

        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expects, $this->model->getCatalogTotalPriceWithTax($inQuoteCurrency));
    }

    /**
     * Data Provider for getCatalogTotalPriceWithoutTax() method.
     *
     * @return array
     */
    public function dataProviderGetCatalogTotalPriceWithTax()
    {
        return [
            [
                false, 200, 3, null, 15, 645,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                false, 10, 5, null, 5, 75,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                true, 200, 3, 1.5, 15, 967.5,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                true, 10, 5, 1.4, 5, 105,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ]
        ];
    }

    /**
     * Test getCartTotalDiscount() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $discount|null
     * @param float $qty
     * @param float $currencyRate
     * @param float $expects
     * @param array $calls
     * @dataProvider dataProviderGetCartTotalDiscount
     * @return void
     */
    public function testGetCartTotalDiscount($inQuoteCurrency, $discount, $qty, $currencyRate, $expects, array $calls)
    {
        $this->prepareQuoteItems($qty);

        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, 33, 0);
        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT, $discount, 1);

        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expects, $this->model->getCartTotalDiscount($inQuoteCurrency));
    }

    /**
     * Data Provider for getCartTotalDiscount().
     *
     * @return array
     */
    public function dataProviderGetCartTotalDiscount()
    {
        return [
            [
                false, 30, 3, null, 90,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                false, 2, 5, null, 10,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                true, 30, 3, 1.5, 135,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                true, 2, 5, 3.5, 35,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ]
        ];
    }

    /**
     * Test for getCatalogTotalPrice() method.
     *
     * @param bool $inQuoteCurrency
     * @param bool $isTaxDisplayedWithGrandTotal
     * @param float $expects
     * @param array $calls
     * @dataProvider getCatalogTotalPriceDataProvider
     * @return void
     */
    public function testGetCatalogTotalPrice($inQuoteCurrency, $isTaxDisplayedWithGrandTotal, $expects, array $calls)
    {
        $price = 120;
        $qty = 3;
        $discount = 16;
        $tax = 10;
        $currencyRate = 2.5;

        $this->prepareIsTaxDisplayedWithGrandTotal($isTaxDisplayedWithGrandTotal);

        $this->prepareQuoteItems($qty);

        if ($isTaxDisplayedWithGrandTotal) {
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 0);
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 1);
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT, $tax, 2);
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT, $discount, 3);
        } else {
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 0);
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, $price, 1);
            $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT, $discount, 2);
        }

        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expects, $this->model->getCatalogTotalPrice($inQuoteCurrency));
    }

    /**
     * Data provider for getCatalogTotalPrice() method.
     *
     * @return array
     */
    public function getCatalogTotalPriceDataProvider()
    {
        return [
            [
                true, true, 855,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                false, true, 342,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                true, false, 780,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                false, false, 312,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
        ];
    }

    /**
     * Test getOriginalTaxValue() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $tax
     * @param float $qty
     * @param float $expected
     * @param array $calls
     * @dataProvider dataProviderGetOriginalTaxValue
     * @return void
     */
    public function testGetOriginalTaxValue($inQuoteCurrency, $tax, $qty, $expected, array $calls)
    {
        $currencyRate = 2;

        $this->prepareQuoteItems($qty);

        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_PRICE, 33.0, 0);
        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT, $tax, 1);

        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expected, $this->model->getOriginalTaxValue($inQuoteCurrency));
    }

    /**
     * Test for getQuoteVisibleItems() method.
     *
     * @return void
     */
    public function testGetQuoteVisibleItems()
    {
        $quoteItemId = 1;
        $this->quoteItemModelMock->expects($this->any())->method('getItemId')->willReturn($quoteItemId);
        $negotiableQuoteItemMock = $this->getMockBuilder(NegotiableQuoteItemInterface::class)
            ->setMethods([
                'load',
                'setItemId',
                'getOriginalPrice',
                'getData'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $negotiableQuoteItemMock->expects($this->once())->method('getOriginalPrice')->willReturn(null);
        $negotiableQuoteItemMock->expects($this->once())->method('setItemId')->with($quoteItemId);
        $negotiableQuoteItemMock->expects($this->once())->method('load')->willReturnSelf();
        $negotiableQuoteItemMock->expects($this->any())->method('getData')
            ->with(NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT)->willReturn(1);
        $cartItemExtensionMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtension::class)
            ->setMethods(['setNegotiableQuoteItem'])
            ->disableOriginalConstructor()->getMock();
        $this->extensionFactory->expects($this->any())->method('create')
            ->willReturn($cartItemExtensionMock);
        $cartItemExtensionMock->expects($this->any())->method('setNegotiableQuoteItem')
            ->with($negotiableQuoteItemMock)->willReturnSelf();
        $this->negotiableQuoteItemFactoryMock->expects($this->once())->method('create')
            ->willReturn($negotiableQuoteItemMock);
        $this->extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuoteItem')
            ->willReturn($negotiableQuoteItemMock);
        $this->quoteItemModelMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->quoteMock->expects($this->atLeastOnce())->method('getAllVisibleItems')
            ->willReturn([$this->quoteItemModelMock]);

        $this->model->getOriginalTaxValue();
    }

    /**
     * Data provider getCatalogTotalPriceWithoutTax().
     *
     * @return array
     */
    public function dataProviderGetOriginalTaxValue()
    {
        return [
            [
                true, 15, 3, 90,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                true, 5, 5, 50,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                false, 15, 3, 45,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ],
            [
                false, 5, 5, 25,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ]
        ];
    }

    /**
     * Test getQuoteTotalPrice() method.
     *
     * @param float $subtotal
     * @param bool $isTaxDisplayed
     * @param bool $isQuoteVirtual
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param float $shippingTaxAmount
     * @param float $baseShippingTaxAmount
     * @param float $totals
     * @param array $calls
     * @return void
     * @dataProvider dataProviderGetQuoteTotalPrice
     */
    public function testGetQuoteTotalPrice(
        $subtotal,
        $isTaxDisplayed,
        $isQuoteVirtual,
        $taxAmount,
        $baseTaxAmount,
        $shippingTaxAmount,
        $baseShippingTaxAmount,
        $totals,
        array $calls
    ) {
        $this->quoteMock->expects($this->atLeastOnce())->method('getBaseSubtotalWithDiscount')->willReturn($subtotal);

        $this->prepareIsTaxDisplayedWithGrandTotal($isTaxDisplayed);

        $this->prepareIsQuoteVirtual($isQuoteVirtual, $isTaxDisplayed);

        $this->prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, $calls['billing']);

        $this->prepareQuoteShippingAddressMock(
            $taxAmount,
            $baseTaxAmount,
            $calls['shipping'],
            $shippingTaxAmount,
            $baseShippingTaxAmount
        );

        $this->assertEquals($totals, $this->model->getQuoteTotalPrice());
    }

    /**
     * Data Provider for getQuoteTotalPrice() method.
     *
     * @return array
     */
    public function dataProviderGetQuoteTotalPrice()
    {
        return [
            [500, false, false, 50, 20, 100, 50, 500, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 0
                ]
            ]],
            [500, true, false, 50, 20, 100, 50, 470, [
                'shipping' => [
                    'getShippingAddress' => 2,
                    'getBaseShippingTaxAmount' => 1,
                    'getBaseTaxAmount' => 1,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [500, true, true, 50, 20, 100, 50, 520, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 1,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]]
        ];
    }

    /**
     * Test getQuoteShippingPrice() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $shippingPrice
     * @param float $shippingAmount
     * @param float $baseShippingAmount
     * @param float $expected
     * @param array $calls
     * @dataProvider getQuoteShippingPriceDataProvider
     * @return void
     */
    public function testGetQuoteShippingPrice(
        $inQuoteCurrency,
        $shippingPrice,
        $shippingAmount,
        $baseShippingAmount,
        $expected,
        array $calls
    ) {
        $this->negotiableQuoteMock->expects($this->exactly($calls['getShippingPrice']))
            ->method('getShippingPrice')->willReturn($shippingPrice);
        $this->prepareNegotiableQuoteExtensionAttributesMock();

        $currencyRate = 1.2;
        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->shippingAddressMock->expects($this->exactly($calls['getShippingAmount']))
            ->method('getShippingAmount')
            ->willReturn($shippingAmount);

        $this->shippingAddressMock->expects($this->exactly($calls['getBaseShippingAmount']))
            ->method('getBaseShippingAmount')
            ->willReturn($baseShippingAmount);

        $this->quoteMock->expects($this->exactly($calls['getShippingAddress']))
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddressMock);

        $this->assertEquals($expected, $this->model->getQuoteShippingPrice($inQuoteCurrency));
    }

    /**
     * Data provider for getQuoteShippingPrice() method.
     *
     * @return array
     */
    public function getQuoteShippingPriceDataProvider()
    {
        return [
            [
                true, 150, null, null, 180,
                ['getShippingAmount' => 0, 'getBaseShippingAmount' => 0, 'getShippingAddress' => 0,
                    'getShippingPrice' => 2, 'quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1
                ]
            ],
            [
                false, 150, null, null, 150,
                ['getShippingAmount' => 0, 'getBaseShippingAmount' => 0, 'getShippingAddress' => 0,
                    'getShippingPrice' => 2, 'quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0,
                ]
            ],
            [
                true, null, 150, 100, 150,
                ['getShippingAmount' => 1, 'getBaseShippingAmount' => 0, 'getShippingAddress' => 2,
                    'getShippingPrice' => 1, 'quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0
                ]
            ],
            [
                false, null, 150, 100, 100,
                ['getShippingAmount' => 0, 'getBaseShippingAmount' => 1, 'getShippingAddress' => 2,
                    'getShippingPrice' => 1, 'quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0
                ]
            ]
        ];
    }

    /**
     * Test getQuote() method.
     *
     * @return void
     */
    public function testGetQuote()
    {
        $this->assertEquals($this->quoteMock, $this->model->getQuote());
    }

    /**
     * Test isTaxDisplayedWithGrandTotal() method.
     *
     * @param bool $isTaxDisplayedWithGrandTotal
     * @dataProvider dataProviderIsTaxDisplayedWithGrandTotal
     * @return void
     */
    public function testIsTaxDisplayedWithGrandTotal($isTaxDisplayedWithGrandTotal)
    {
        $this->prepareIsTaxDisplayedWithGrandTotal($isTaxDisplayedWithGrandTotal);

        $this->assertEquals($isTaxDisplayedWithGrandTotal, $this->model->isTaxDisplayedWithGrandTotal());
    }

    /**
     * DataProvider for isTaxDisplayedWithGrandTotal() method.
     *
     * @return array
     */
    public function dataProviderIsTaxDisplayedWithGrandTotal()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Test isTaxDisplayedWithSubtotal() method.
     *
     * @param bool $isDisplaySalesSubtotalInclTax
     * @param bool $isDisplaySalesSubtotalBoth
     * @param bool $isTaxDisplayedWithSubtotal
     * @dataProvider dataProviderIsTaxDisplayedWithSubtotal
     * @return void
     */
    public function testIsTaxDisplayedWithSubtotal(
        $isDisplaySalesSubtotalInclTax,
        $isDisplaySalesSubtotalBoth,
        $isTaxDisplayedWithSubtotal
    ) {
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $this->taxConfigMock->expects($this->atLeastOnce())->method('displaySalesSubtotalInclTax')
            ->willReturn($isDisplaySalesSubtotalInclTax);
        $qty = ($isDisplaySalesSubtotalInclTax && $isTaxDisplayedWithSubtotal) ? 0 : 1;
        $this->taxConfigMock->expects($this->exactly($qty))->method('displaySalesSubtotalBoth')
            ->willReturn($isDisplaySalesSubtotalBoth);

        $this->assertEquals($isTaxDisplayedWithSubtotal, $this->model->isTaxDisplayedWithSubtotal());
    }

    /**
     * Data Provider for isTaxDisplayedWithSubtotal() method.
     *
     * @return array
     */
    public function dataProviderIsTaxDisplayedWithSubtotal()
    {
        return [
            [false, false, false],
            [true, false, true],
            [false, true, true],
            [true, true, true]
        ];
    }

    /**
     * Test getSubtotalTaxValue() method.
     *
     * @param bool $inQuoteCurrency
     * @param bool $isQuoteVirtual
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param float $shippingTaxAmount
     * @param float $baseShippingTaxAmount
     * @param float $expected
     * @param array $calls
     * @dataProvider dataProviderGetSubtotalTaxValue
     * @return void
     */
    public function testGetSubtotalTaxValue(
        $inQuoteCurrency,
        $isQuoteVirtual,
        $taxAmount,
        $baseTaxAmount,
        $shippingTaxAmount,
        $baseShippingTaxAmount,
        $expected,
        array $calls
    ) {
        $this->prepareIsQuoteVirtual($isQuoteVirtual);

        $this->prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, $calls['billing']);

        $this->prepareQuoteShippingAddressMock(
            $taxAmount,
            $baseTaxAmount,
            $calls['shipping'],
            $shippingTaxAmount,
            $baseShippingTaxAmount
        );

        $this->assertEquals($expected, $this->model->getSubtotalTaxValue($inQuoteCurrency));
    }

    /**
     * Data Provider for getSubtotalTaxValue() method.
     *
     * @return array
     */
    public function dataProviderGetSubtotalTaxValue()
    {
        return [
            [false, false, 30, 20, 30, 25, -5, [
                'shipping' => [
                    'getShippingAddress' => 2,
                    'getBaseShippingTaxAmount' => 1,
                    'getBaseTaxAmount' => 1,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [false, true, 30, 20, 20, 25, 20, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 1,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [true, false, 30, 20, 30, 25, 0, [
                'shipping' => [
                    'getShippingAddress' => 2,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 1,
                    'getShippingTaxAmount' => 1
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [true, true, 30, 20, 20, 25, 30, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 1,
                    'getBillingAddress' => 1
                ]
            ]]
        ];
    }

    /**
     * Prepare Quote Billing Address Mock.
     *
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param array $calls
     * @return void
     */
    private function prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, array $calls)
    {
        $this->billingAddressMock->expects($this->exactly($calls['getBaseTaxAmountBilling']))
            ->method('getBaseTaxAmount')->willReturn($baseTaxAmount);
        $this->billingAddressMock->expects($this->exactly($calls['getTaxAmountBilling']))
            ->method('getTaxAmount')->willReturn($taxAmount);

        $this->quoteMock->expects($this->exactly($calls['getBillingAddress']))
            ->method('getBillingAddress')->willReturn($this->billingAddressMock);
    }

    /**
     * Prepare Quote Shipping Address Mock.
     *
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param array $calls
     * @param float|int $shippingTaxAmount [optional]
     * @param float|int $baseShippingTaxAmount [optional]
     * @return void
     */
    private function prepareQuoteShippingAddressMock(
        $taxAmount,
        $baseTaxAmount,
        array $calls,
        $shippingTaxAmount = 0,
        $baseShippingTaxAmount = 0
    ) {
        $this->shippingAddressMock->expects($this->exactly($calls['getBaseTaxAmount']))
            ->method('getBaseTaxAmount')->willReturn($baseTaxAmount);
        $this->shippingAddressMock->expects($this->exactly($calls['getTaxAmount']))
            ->method('getTaxAmount')->willReturn($taxAmount);

        $this->shippingAddressMock->expects($this->exactly($calls['getShippingTaxAmount']))
            ->method('getShippingTaxAmount')->willReturn($shippingTaxAmount);

        $this->shippingAddressMock->expects($this->exactly($calls['getBaseShippingTaxAmount']))
            ->method('getBaseShippingTaxAmount')->willReturn($baseShippingTaxAmount);

        $this->quoteMock->expects($this->exactly($calls['getShippingAddress']))
            ->method('getShippingAddress')->willReturn($this->shippingAddressMock);
    }

    /**
     * Test getTaxValue() method.
     *
     * @param bool $inQuoteCurrency
     * @param bool $isQuoteVirtual
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param float $expected
     * @param array $calls
     * @dataProvider dataProviderGetTaxValue
     * @return void
     */
    public function testGetTaxValue(
        $inQuoteCurrency,
        $isQuoteVirtual,
        $taxAmount,
        $baseTaxAmount,
        $expected,
        array $calls
    ) {
        $this->prepareIsQuoteVirtual($isQuoteVirtual);

        $this->prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, $calls['billing']);

        $this->prepareQuoteShippingAddressMock($taxAmount, $baseTaxAmount, $calls['shipping']);

        $this->assertEquals($expected, $this->model->getTaxValue($inQuoteCurrency));
    }

    /**
     * Data Provider getTaxValue() method.
     *
     * @return array
     */
    public function dataProviderGetTaxValue()
    {
        return [
            [false,false, 30, 20, 20, [
                'shipping' => [
                    'getShippingAddress' => 1,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 1,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [false,true, 30, 20, 20, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 1,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [true, false, 30, 20, 30, [
                'shipping' => [
                    'getShippingAddress' => 1,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 1,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [true,true, 16, 5, 16, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 1,
                    'getBillingAddress' => 1
                ]
            ]]
        ];
    }

    /**
     * Test getShippingTaxValue().
     *
     * @param bool $inQuoteCurrency
     * @param bool $isQuoteVirtual
     * @param float $shippingTaxAmount
     * @param float $baseShippingTaxAmount
     * @param float $expects
     * @param array $calls
     * @dataProvider dataProviderGetShippingTaxValue
     * @return void
     */
    public function testGetShippingTaxValue(
        $inQuoteCurrency,
        $isQuoteVirtual,
        $shippingTaxAmount,
        $baseShippingTaxAmount,
        $expects,
        array $calls
    ) {
        $this->prepareIsQuoteVirtual($isQuoteVirtual);

        $this->prepareQuoteShippingAddressMock(0, 0, $calls, $shippingTaxAmount, $baseShippingTaxAmount);

        $this->assertEquals($expects, $this->model->getShippingTaxValue($inQuoteCurrency));
    }

    /**
     * Data Provider for getShippingTaxValue().
     *
     * @return array
     */
    public function dataProviderGetShippingTaxValue()
    {
        return [
            [true, false, 10, 15, 10, [
                'getShippingAddress' => 1, 'getBaseShippingTaxAmount' => 0, 'getBaseTaxAmount' => 0,
                'getTaxAmount' => 0, 'getShippingTaxAmount' => 1
            ]],
            [true, true, 10, 15, 0, [
                'getShippingAddress' => 0, 'getBaseShippingTaxAmount' => 0, 'getBaseTaxAmount' => 0,
                'getTaxAmount' => 0, 'getShippingTaxAmount' => 0
            ]],
            [false, false, 10, 15, 15, [
                'getShippingAddress' => 1, 'getBaseShippingTaxAmount' => 1, 'getBaseTaxAmount' => 0,
                'getTaxAmount' => 0, 'getShippingTaxAmount' => 0
            ]],
            [false, true, 10, 15, 0, [
                'getShippingAddress' => 0, 'getBaseShippingTaxAmount' => 0, 'getBaseTaxAmount' => 0,
                'getTaxAmount' => 0, 'getShippingTaxAmount' => 0
            ]]
        ];
    }

    /**
     * Test getTaxValueForAddInTotal().
     *
     * @param bool $isTaxDisplayedWithGrandTotal
     * @param bool $isQuoteVirtual
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param bool $isShippingPriceIncludesTax
     * @param float $shippingTaxAmount
     * @param float $baseShippingTaxAmount
     * @param float $expected
     * @param array $calls
     * @dataProvider dataProviderGetTaxValueForAddInTotal
     * @return void
     */
    public function testGetTaxValueForAddInTotal(
        $isTaxDisplayedWithGrandTotal,
        $isQuoteVirtual,
        $taxAmount,
        $baseTaxAmount,
        $isShippingPriceIncludesTax,
        $shippingTaxAmount,
        $baseShippingTaxAmount,
        $expected,
        array $calls
    ) {
        $this->prepareIsTaxDisplayedWithGrandTotal($isTaxDisplayedWithGrandTotal);
        $this->taxConfigMock->expects($this->atLeastOnce())
            ->method('shippingPriceIncludesTax')->willReturn($isShippingPriceIncludesTax);

        $this->prepareIsQuoteVirtual($isQuoteVirtual, $calls['isQuoteVirtual']);

        $this->prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, $calls['billing']);

        $this->prepareQuoteShippingAddressMock(
            $taxAmount,
            $baseTaxAmount,
            $calls['shipping'],
            $shippingTaxAmount,
            $baseShippingTaxAmount
        );

        $this->assertEquals($expected, $this->model->getTaxValueForAddInTotal());
    }

    /**
     * Data Provider getTaxValueForAddInTotal().
     *
     * @return array
     */
    public function dataProviderGetTaxValueForAddInTotal()
    {
        return [
            [false, false, 30, 20, false, 7, 5, 20, [
                'shipping' => [
                    'getShippingAddress' => 3,
                    'getBaseShippingTaxAmount' => 2,
                    'getBaseTaxAmount' => 1,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 1,
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [false, false, 30, 20, true, 7, 5, 15, [
                'shipping' => [
                    'getShippingAddress' => 2,
                    'getBaseShippingTaxAmount' => 1,
                    'getBaseTaxAmount' => 1,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 1,
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [false, true, 30, 20, true, 7, 5, 20, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 1,
                'billing' => [
                    'getBaseTaxAmountBilling' => 1,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 1
                ]
            ]],
            [true, true, 30, 20, true, 7, 5, 0, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 0,
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 0
                ]
            ]],
            [true, true, 30, 20, false, 7, 5, 0, [
                'shipping' => [
                    'getShippingAddress' => 0,
                    'getBaseShippingTaxAmount' => 0,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 1,
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 0
                ]
            ]],
            [true, false, 30, 20, false, 7, 5, 5, [
                'shipping' => [
                    'getShippingAddress' => 1,
                    'getBaseShippingTaxAmount' => 1,
                    'getBaseTaxAmount' => 0,
                    'getTaxAmount' => 0,
                    'getShippingTaxAmount' => 0
                ],
                'isQuoteVirtual' => 1,
                'billing' => [
                    'getBaseTaxAmountBilling' => 0,
                    'getTaxAmountBilling' => 0,
                    'getBillingAddress' => 0
                ]
            ]]
        ];
    }

    /**
     * Test for getSubtotal() method.
     *
     * @param bool $inQuoteCurrency
     * @param bool $displaySalesTaxWithGrandTotal
     * @param bool $isQuoteVirtual
     * @param bool $isVirtualQuoteUsed
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param float $expectedResult
     * @param array $calls
     * @dataProvider getSubtotalDataProvider
     * @return void
     */
    public function testGetSubtotal(
        $inQuoteCurrency,
        $displaySalesTaxWithGrandTotal,
        $isQuoteVirtual,
        $isVirtualQuoteUsed,
        $taxAmount,
        $baseTaxAmount,
        $expectedResult,
        array $calls
    ) {
        $subtotalWithDiscount = 130;
        $baseSubtotalWithDiscount = 160;
        $this->prepareGetOriginalSubtotalMethod(
            $isQuoteVirtual,
            $isVirtualQuoteUsed,
            $displaySalesTaxWithGrandTotal,
            $subtotalWithDiscount,
            $baseSubtotalWithDiscount,
            $taxAmount,
            $baseTaxAmount,
            $calls
        );

        $this->assertEquals($expectedResult, $this->model->getSubtotal($inQuoteCurrency));
    }

    /**
     * Data provider for getSubtotal() method.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getSubtotalDataProvider()
    {
        return [
            [
                false,
                false,
                true,
                false,
                20,
                15,
                160,
                [
                    'getSubtotalWithDiscount' => 0,
                    'getBaseSubtotalWithDiscount' => 1,
                    'shipping' => [
                        'getShippingAddress' => 0,
                        'getBaseShippingTaxAmount' => 0,
                        'getBaseTaxAmount' => 0,
                        'getTaxAmount' => 0,
                        'getShippingTaxAmount' => 0,
                    ],
                    'billing' => [
                        'getBaseTaxAmountBilling' => 0,
                        'getTaxAmountBilling' => 0,
                        'getBillingAddress' => 0
                    ]
                ],
            ],
            [
                false,
                true,
                true,
                true,
                20,
                15,
                175,
                [
                    'getSubtotalWithDiscount' => 0,
                    'getBaseSubtotalWithDiscount' => 1,
                    'shipping' => [
                        'getShippingAddress' => 0,
                        'getBaseShippingTaxAmount' => 0,
                        'getBaseTaxAmount' => 0,
                        'getTaxAmount' => 0,
                        'getShippingTaxAmount' => 0
                    ],
                    'billing' => [
                        'getBaseTaxAmountBilling' => 1,
                        'getTaxAmountBilling' => 0,
                        'getBillingAddress' => 1
                    ]
                ]
            ],
            [
                false,
                true,
                false,
                true,
                20,
                15,
                159,
                [
                    'getSubtotalWithDiscount' => 0,
                    'getBaseSubtotalWithDiscount' => 1,
                    'shipping' => [
                        'getShippingAddress' => 2,
                        'getBaseShippingTaxAmount' => 1,
                        'getBaseTaxAmount' => 1,
                        'getTaxAmount' => 0,
                        'getShippingTaxAmount' => 0
                    ],
                    'billing' => [
                        'getBaseTaxAmountBilling' => 0,
                        'getTaxAmountBilling' => 0,
                        'getBillingAddress' => 1
                    ]
                ]
            ],
            [
                true,
                false,
                true,
                false,
                20,
                15,
                130,
                [
                    'getSubtotalWithDiscount' => 1,
                    'getBaseSubtotalWithDiscount' => 0,
                    'shipping' => [
                        'getShippingAddress' => 0,
                        'getBaseShippingTaxAmount' => 0,
                        'getBaseTaxAmount' => 0,
                        'getTaxAmount' => 0,
                        'getShippingTaxAmount' => 0
                    ],
                    'billing' => [
                        'getBaseTaxAmountBilling' => 0,
                        'getTaxAmountBilling' => 0,
                        'getBillingAddress' => 0
                    ]
                ]
            ],
            [
                true,
                true,
                true,
                true,
                20,
                15,
                150,
                [
                    'getSubtotalWithDiscount' => 1,
                    'getBaseSubtotalWithDiscount' => 0,
                    'shipping' => [
                        'getShippingAddress' => 0,
                        'getBaseShippingTaxAmount' => 0,
                        'getBaseTaxAmount' => 0,
                        'getTaxAmount' => 0,
                        'getShippingTaxAmount' => 0
                    ],
                    'billing' => [
                        'getBaseTaxAmountBilling' => 0,
                        'getTaxAmountBilling' => 1,
                        'getBillingAddress' => 1
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider getSubtotal() with Proposed Price Type.
     *
     * @return array
     */
    public function getSubtotalWithProposedPriceTypeDataProvider()
    {
        return [
            [
                true, 329,
                ['quoteGetCurrency' => 1, 'currencyGetBaseToQuoteRate' => 1]
            ],
            [
                false, 235,
                ['quoteGetCurrency' => 0, 'currencyGetBaseToQuoteRate' => 0]
            ]
        ];
    }

    /**
     * Prepare Extensions Attributes Mock for Negotiable quote.
     *
     * @return void
     */
    private function prepareNegotiableQuoteExtensionAttributesMock()
    {
        $this->extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuoteMock);

        $this->quoteMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
    }

    /**
     * Prepare getOriginalSubtotal() method.
     *
     * @param bool $isQuoteVirtual
     * @param bool $isVirtualQuoteUsed
     * @param bool $displaySalesTaxWithGrandTotal
     * @param float $subtotalWithDiscount
     * @param float $baseSubtotalWithDiscount
     * @param float $taxAmount
     * @param float $baseTaxAmount
     * @param array $calls
     * @return void
     */
    private function prepareGetOriginalSubtotalMethod(
        $isQuoteVirtual,
        $isVirtualQuoteUsed,
        $displaySalesTaxWithGrandTotal,
        $subtotalWithDiscount,
        $baseSubtotalWithDiscount,
        $taxAmount,
        $baseTaxAmount,
        array $calls
    ) {
        $this->quoteMock->expects($this->exactly($calls['getSubtotalWithDiscount']))
            ->method('getSubtotalWithDiscount')->willReturn($subtotalWithDiscount);
        $this->quoteMock->expects($this->exactly($calls['getBaseSubtotalWithDiscount']))
            ->method('getBaseSubtotalWithDiscount')->willReturn($baseSubtotalWithDiscount);

        $this->prepareIsTaxDisplayedWithGrandTotal($displaySalesTaxWithGrandTotal);

        $this->prepareIsQuoteVirtual($isQuoteVirtual, $isVirtualQuoteUsed);

        $this->prepareQuoteBillingAddressMock($taxAmount, $baseTaxAmount, $calls['billing']);

        $shippingTaxAmount = 23;
        $baseShippingTaxAmount = 16;
        $this->prepareQuoteShippingAddressMock(
            $taxAmount,
            $baseTaxAmount,
            $calls['shipping'],
            $shippingTaxAmount,
            $baseShippingTaxAmount
        );
    }

    /**
     * Prepare Quote Grand Total.
     *
     * @param float|null $grandTotal
     * @param float|null $baseGrandTotal
     * @param array $calls
     * @return void
     */
    private function prepareQuoteGrandTotal($grandTotal, $baseGrandTotal, array $calls)
    {
        $this->quoteMock->expects($this->exactly($calls['getGrandTotal']))->method('getGrandTotal')
            ->willReturn($grandTotal);
        $this->quoteMock->expects($this->exactly($calls['getBaseGrandTotal']))->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);
    }

    /**
     * Test getGrandTotal() method.
     *
     * @param bool $inQuoteCurrency
     * @param float $expected
     * @param array $calls
     * @dataProvider getGrandTotalDataProvider
     * @return void
     */
    public function testGetGrandTotal($inQuoteCurrency, $expected, array $calls)
    {
        $this->prepareQuoteGrandTotal($expected, $expected, $calls);

        $this->assertEquals($expected, $this->model->getGrandTotal($inQuoteCurrency));
    }

    /**
     * Data provider for getGrandTotal() method.
     *
     * @return array
     */
    public function getGrandTotalDataProvider()
    {
        return [
            [
                true, 260, ['getGrandTotal' => 1, 'getBaseGrandTotal' => 0]
            ],
            [
                false, 340, ['getGrandTotal' => 0, 'getBaseGrandTotal' => 1]
            ]
        ];
    }

    /**
     * Test getTotalCost() method.
     *
     * @return void
     */
    public function testGetTotalCost()
    {
        $this->quoteMock->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$this->quoteItemModelMock]);
        $productMock = $this->getProductMock(20, 1);
        $this->quoteItemModelMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->quoteItemModelMock->expects($this->once())->method('getQty')->willReturn(2);
        $this->assertEquals(40, $this->model->getTotalCost());
    }

    /**
     * Test getItemCost() method.
     *
     * @param array $children
     * @param bool $inQuoteCurrency
     * @param float $currencyRate
     * @param float $quoteItemProductCost
     * @param float $expected
     * @param array $calls
     * @dataProvider getItemCostDataProvider
     * @return void
     */
    public function testGetItemCost(
        array $children,
        $inQuoteCurrency,
        $currencyRate,
        $quoteItemProductCost,
        $expected,
        array $calls
    ) {
        $productMock = $this->getProductMock($quoteItemProductCost, $calls['getCost']);
        $quoteItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->setMethods([
                'getChildren',
                'getProduct'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quoteItem->expects($this->exactly(1))->method('getChildren')->willReturn($children);
        $quoteItem->expects($this->exactly($calls['itemGetProduct']))->method('getProduct')
            ->willReturn($productMock);
        $this->prepareQuoteCurrencyMock($currencyRate, $calls);

        $this->assertEquals($expected, $this->model->getItemCost($quoteItem, $inQuoteCurrency));
    }

    /**
     * Data provider getItemCost() method.
     *
     * @return array
     */
    public function getItemCostDataProvider()
    {
        $childProductCost = 20;
        $childProductQty = 2;

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getCost'])
            ->disableOriginalConstructor()->getMock();
        $product->expects($this->exactly(2))->method('getCost')->willReturn($childProductCost);

        $child = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\AbstractItem::class)
            ->setMethods([
                'getProduct',
                'getQty'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $child->expects($this->any())->method('getProduct')->willReturn($product);
        $child->expects($this->any())->method('getQty')->willReturn($childProductQty);

        $children = [$child];

        $currencyRate = 1.4;

        return [
            [
                $children,
                false,
                $currencyRate,
                $childProductCost * $childProductQty,
                $childProductCost * $childProductQty,
                ['getCost' => 0, 'itemGetProduct' => 0, 'currencyGetBaseToQuoteRate' => 0, 'quoteGetCurrency' => 0]
            ],
            [
                $children,
                true,
                $currencyRate,
                $childProductCost * $childProductQty,
                $childProductCost * $childProductQty * $currencyRate,
                ['getCost' => 0, 'itemGetProduct' => 0, 'currencyGetBaseToQuoteRate' => 1, 'quoteGetCurrency' => 1]
            ],
            [
                [],
                false,
                $currencyRate,
                40,
                40,
                ['getCost' => 1, 'itemGetProduct' => 1, 'currencyGetBaseToQuoteRate' => 0, 'quoteGetCurrency' => 0]
            ],
            [
                [],
                true,
                $currencyRate,
                40,
                40 * $currencyRate,
                ['getCost' => 1, 'itemGetProduct' => 1, 'currencyGetBaseToQuoteRate' => 1, 'quoteGetCurrency' => 1]
            ]
        ];
    }

    /**
     * Prepare Product Mock with cost method mock.
     *
     * @param float|int $value
     * @param int $callsQty [optional]
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock($value, $callsQty = 1)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getCost'])
            ->disableOriginalConstructor()->getMock();
        $productMock->expects($this->exactly($callsQty))
            ->method('getCost')->willReturn($value);
        return $productMock;
    }

    /**
     * Prepare Quote Items.
     *
     * @param float $qty
     * @return void
     */
    private function prepareQuoteItems($qty)
    {
        $this->extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuoteItem')
            ->willReturn($this->negotiableQuoteItemMock);
        $this->quoteItemModelMock->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->quoteItemModelMock->expects($this->atLeastOnce())->method('getQty')->willReturn($qty);
        $quoteItemsIterator = new \ArrayIterator([$this->quoteItemModelMock]);
        $this->quoteMock->expects($this->atLeastOnce())->method('getAllVisibleItems')->willReturn($quoteItemsIterator);
    }

    /**
     * Get Original Tax Value.
     *
     * @param float $tax
     * @param int $callNumber [optional]
     * @return void
     */
    private function getOriginalTaxValue($tax, $callNumber = 0)
    {
        $this->prepareGetDataValue(NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT, $tax, $callNumber);
    }

    /**
     * Fetch Get Data Value.
     *
     * @param string $type
     * @param float $value
     * @param int $callNumber [optional]
     * @return void
     */
    private function prepareGetDataValue($type, $value, $callNumber = 0)
    {
        $this->negotiableQuoteItemMock->expects($this->at($callNumber))->method('getData')
            ->with($type)->willReturn($value);
    }

    /**
     * Prepare mock function isQuoteVirtual.
     *
     * @param bool $isQuoteVirtual
     * @param bool $isFunctionUsed [optional]
     * @return void
     */
    private function prepareIsQuoteVirtual($isQuoteVirtual, $isFunctionUsed = true)
    {
        if ($isFunctionUsed) {
            $this->quoteMock->expects($this->atLeastOnce())
                ->method('isVirtual')->willReturn($isQuoteVirtual ? true : false);
        }
    }

    /**
     * Prepare IsTaxDisplayedWithGrandTotal.
     *
     * @param bool $isTaxDisplayedWithGrandTotal
     * @return void
     */
    private function prepareIsTaxDisplayedWithGrandTotal($isTaxDisplayedWithGrandTotal)
    {
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->atLeastOnce())->method('getStore')->willReturn($storeMock);
        $this->taxConfigMock->expects($this->atLeastOnce())->method('displaySalesTaxWithGrandTotal')
            ->willReturn($isTaxDisplayedWithGrandTotal);
    }
}
