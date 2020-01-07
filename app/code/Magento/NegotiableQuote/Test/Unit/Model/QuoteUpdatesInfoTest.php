<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for QuoteUpdatesInfoTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteUpdatesInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdatesInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteUpdatesInfo;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteTotalsFactoryMock;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelperMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteItemManagementMock;

    /**
     * @var \Magento\Framework\Filter\StripTags|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagFilterMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\NegotiableQuote\Model\QuoteUpdatesInfo\ProductOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productOptionsMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Status\LabelProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelProviderMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageProviderMock;

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
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->setMethods(['format'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->quoteTotalsFactoryMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\TotalsFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->quoteHelperMock = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->setMethods(['getStockForProduct', 'retrieveCustomOptions', 'isLockMessageDisplayed'])
            ->disableOriginalConstructor()->getMock();

        $this->negotiableQuoteItemManagementMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface::class)
            ->setMethods(['getOriginalPriceByItem'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->tagFilterMock = $this->getMockBuilder(\Magento\Framework\Filter\StripTags::class)
            ->disableOriginalConstructor()->getMock();

        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->productOptionsMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\QuoteUpdatesInfo\ProductOptions::class)
            ->setMethods(['getConfigurationForProductType', 'getFormattedOptionValue'])
            ->disableOriginalConstructor()->getMock();

        $this->labelProviderMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Status\LabelProviderInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->messageProviderMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Discount\StateChanges\Provider::class)
            ->setMethods(['getChangesMessages'])
            ->disableOriginalConstructor()->getMock();

        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods([
                'collectTotals',
                'getExtensionAttributes',
                'getId',
                'getItemsCollection',
                'getCurrency',
                'getShippingAddress'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->quoteUpdatesInfo = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Model\QuoteUpdatesInfo::class,
            [
                'priceCurrency' => $this->priceCurrencyMock,
                'quoteTotalsFactory' => $this->quoteTotalsFactoryMock,
                'quoteHelper' => $this->quoteHelperMock,
                'negotiableQuoteItemManagement' => $this->negotiableQuoteItemManagementMock,
                'tagFilter' => $this->tagFilterMock,
                'url' => $this->urlMock,
                'productOptions' => $this->productOptionsMock,
                'labelProvider' => $this->labelProviderMock,
                'messageProvider' => $this->messageProviderMock
            ]
        );
    }

    /**
     * Get Quote Item mock.
     *
     * @param int $itemId
     * @param bool $hasMessageError
     * @param array $returned
     * @param array $calls
     * @return \Magento\Quote\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($itemId, $hasMessageError, array $returned, array $calls)
    {
        $isDeletedItem = false;
        $parentItem = null;

        $quoteItem = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->setMethods([
                'isDeleted',
                'getParentItem',
                'getBasePrice',
                'getBaseDiscountAmount',
                'getQty',
                'getBaseRowTotal',
                'getBaseTaxAmount',
                'getMessage',
                'getExtensionAttributes',
                'getItemId',
                'getProduct',
                'getProductType',
                'getHasError'
            ])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $quoteItem->expects($this->exactly(1))->method('isDeleted')->willReturn($isDeletedItem);
        $quoteItem->expects($this->exactly(1))->method('getParentItem')->willReturn($parentItem);

        $quoteItemBasePrice = 350;
        $quoteItem->expects($this->exactly(1))->method('getBasePrice')->willReturn($quoteItemBasePrice);

        $quoteItemBaseDiscountAmount = 30;
        $quoteItem->expects($this->exactly(3))->method('getBaseDiscountAmount')
            ->willReturn($quoteItemBaseDiscountAmount);

        $quoteItemQty = 3;
        $quoteItem->expects($this->exactly(2))->method('getQty')->willReturn($quoteItemQty);

        $baseRowTotal = 344;
        $quoteItem->expects($this->exactly(2))->method('getBaseRowTotal')->willReturn($baseRowTotal);

        $baseTaxAmount = 45;
        $quoteItem->expects($this->exactly($calls['quoteItem_getBaseTaxAmount']))->method('getBaseTaxAmount')
            ->willReturn($baseTaxAmount);

        $negotiableQuoteItemOriginalPrice = 390;
        $negotiableQuoteItem = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface::class)
            ->setMethods(['getOriginalPrice'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $negotiableQuoteItem->expects($this->exactly(1))->method('getOriginalPrice')
            ->willReturn($negotiableQuoteItemOriginalPrice);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemExtensionInterface::class)
            ->setMethods(['getNegotiableQuoteItem'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $extensionAttributes->expects($this->exactly(2))->method('getNegotiableQuoteItem')
            ->willReturn($negotiableQuoteItem);
        $quoteItem->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $quoteItem->expects($this->exactly(1))->method('getItemId')->willReturn($itemId);

        $product = $this->getProductMock();
        $quoteItem->expects($this->exactly(6))->method('getProduct')->willReturn($product);

        $productType = 'simple';
        $quoteItem->expects($this->exactly(1))->method('getProductType')->willReturn($productType);

        $message = [$returned['quoteItem_getMessage']];
        $quoteItem->expects($this->exactly(1))->method('getMessage')->willReturn($message);

        $quoteItem->expects($this->exactly(1))->method('getHasError')->willReturn($hasMessageError);

        return $quoteItem;
    }

    /**
     * Prepare getItemOptions() method.
     *
     * @param array $returned
     * @param array $calls
     * @return void
     */
    private function prepareGetItemOptionsMethod(array $returned, array $calls)
    {
        $productConfiguration = $this
            ->getMockBuilder(\Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface::class)
            ->setMethods(['getOptions'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $productConfigurationOptions = [
            [
                'label' => 'Test Product'
            ]
        ];
        $productConfiguration->expects($this->exactly(1))->method('getOptions')
            ->willReturn($productConfigurationOptions);

        $this->productOptionsMock->expects($this->exactly(1))->method('getConfigurationForProductType')
            ->willReturn($productConfiguration);

        $this->productOptionsMock->expects($this->exactly(1))->method('getFormattedOptionValue')
            ->willReturn($returned['productOptions_getFormattedOptionValue']);

        $this->tagFilterMock->expects($this->exactly($calls['tagFilter_filter']))->method('filter')
            ->willReturn($returned['tagFilter_filter']);
    }

    /**
     * Get Product mock.
     *
     * @return \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getProductMock()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods([
                'getId',
                'getName',
                'getSku',
                'canConfigure',
                'getData',
                'getProductType'
            ])
            ->disableOriginalConstructor()->getMock();

        $id = 23;
        $product->expects($this->exactly(2))->method('getId')->willReturn($id);

        $name = 'Test Product';
        $product->expects($this->exactly(1))->method('getName')->willReturn($name);

        $sku = 'ASD2356';
        $product->expects($this->exactly(1))->method('getSku')->willReturn($sku);
        $product->expects($this->exactly(1))->method('getData')->with('sku')->willReturn($sku);

        $canConfigure = true;
        $product->expects($this->exactly(1))->method('canConfigure')->willReturn($canConfigure);

        return $product;
    }

    /**
     * Prepare Totals Mock.
     *
     * @param bool $isTaxDisplayedWithSubtotal
     * @return void
     */
    private function prepareTotalsMock($isTaxDisplayedWithSubtotal)
    {
        $totals = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Totals::class)
            ->setMethods([
                'isTaxDisplayedWithSubtotal',
                'getItemCost',
                'getTotalCost',
                'getCatalogTotalPriceWithoutTax',
                'getCatalogTotalPriceWithTax',
                'getCartTotalDiscount',
                'getOriginalTaxValue',
                'getCatalogTotalPrice',
                'getTaxValue',
                'getTaxValueForAddInTotal',
                'getSubtotal',
                'getGrandTotal'
            ])
            ->disableOriginalConstructor()->getMock();

        $totals->expects($this->exactly(1))->method('isTaxDisplayedWithSubtotal')
            ->willReturn($isTaxDisplayedWithSubtotal);

        $itemCost = 327;
        $totals->expects($this->exactly(1))->method('getItemCost')->willReturn($itemCost);

        $totalCost = 400;
        $totals->expects($this->exactly(1))->method('getTotalCost')->willReturn($totalCost);

        $catalogTotalPriceWithoutTax = 350;
        $totals->expects($this->exactly(1))->method('getCatalogTotalPriceWithoutTax')
            ->willReturn($catalogTotalPriceWithoutTax);

        $catalogTotalPriceWithTax = 380;
        $totals->expects($this->exactly(1))->method('getCatalogTotalPriceWithTax')
            ->willReturn($catalogTotalPriceWithTax);

        $cartTotalDiscount = 30;
        $totals->expects($this->exactly(2))->method('getCartTotalDiscount')->willReturn($cartTotalDiscount);

        $originalTaxValue = 35;
        $totals->expects($this->exactly(1))->method('getOriginalTaxValue')->willReturn($originalTaxValue);

        $catalogTotalPrice = 340;
        $totals->expects($this->exactly(3))->method('getCatalogTotalPrice')->willReturn($catalogTotalPrice);

        $taxValue = 30;
        $totals->expects($this->exactly(1))->method('getTaxValue')->willReturn($taxValue);

        $taxValueForAddInTotal = 29;
        $totals->expects($this->exactly(1))->method('getTaxValueForAddInTotal')->willReturn($taxValueForAddInTotal);

        $subtotal = 370;
        $totals->expects($this->exactly(2))->method('getSubtotal')->willReturn($subtotal);

        $grandTotal = 420;
        $totals->expects($this->exactly(2))->method('getGrandTotal')->willReturn($grandTotal);

        $this->quoteTotalsFactoryMock->expects($this->exactly(1))->method('create')->willReturn($totals);
    }

    /**
     * Test getQuoteUpdatedData() method.
     *
     * @param array $quoteData
     * @param bool $isTaxDisplayedWithSubtotal
     * @param string $baseCurrencyCode
     * @param string $quoteCurrencyCode
     * @param float|null $negotiableQuoteShippingPrice
     * @param bool $hasMessageError
     * @param array $returned
     * @param array $expected
     * @param array $calls
     * @dataProvider getQuoteUpdatedDataDataProvider
     * @return void
     */
    public function testGetQuoteUpdatedData(
        array $quoteData,
        $isTaxDisplayedWithSubtotal,
        $baseCurrencyCode,
        $quoteCurrencyCode,
        $negotiableQuoteShippingPrice,
        $hasMessageError,
        array $returned,
        array $expected,
        array $calls
    ) {
        $negotiableQuote = $this->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->setMethods(['getHasUnconfirmedChanges', 'getStatus', 'getShippingPrice'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $hasUnconfirmedChanges = true;
        $negotiableQuote->expects($this->exactly(1))->method('getHasUnconfirmedChanges')
            ->willReturn($hasUnconfirmedChanges);
        $quoteStatus = \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_ORDERED;
        $negotiableQuote->expects($this->exactly(1))->method('getStatus')->willReturn($quoteStatus);

        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $extensionAttributes->expects($this->exactly(4))->method('getNegotiableQuote')->willReturn($negotiableQuote);
        $this->quote->expects($this->exactly(6))->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $quoteId = 35;
        $this->quote->expects($this->exactly(1))->method('getId')->willReturn($quoteId);

        $itemId = null;
        $quoteItem = $this->getQuoteItemMock($itemId, $hasMessageError, $returned, $calls);
        $quoteItemsCollection = [$quoteItem];
        $this->quote->expects($this->exactly(1))->method('getItemsCollection')->willReturn($quoteItemsCollection);

        $this->prepareTotalsMock($isTaxDisplayedWithSubtotal);

        $productUrlByItem = 'test/product';
        $this->urlMock->expects($this->exactly(1))->method('getUrl')->willReturn($productUrlByItem);

        $currency = $this->getMockBuilder(\Magento\Quote\Api\Data\CurrencyInterface::class)
            ->setMethods(['getBaseCurrencyCode', 'getQuoteCurrencyCode', 'getBaseToQuoteRate'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $currency->expects($this->exactly($calls['currency_getBaseCurrencyCode']))->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);
        $currency->expects($this->exactly($calls['currency_getQuoteCurrencyCode']))
            ->method('getQuoteCurrencyCode')->willReturn($quoteCurrencyCode);

        $currency->expects($this->exactly($calls['currency_getBaseToQuoteRate']))->method('getBaseToQuoteRate')
            ->willReturn($returned['currency_getBaseToQuoteRate']);

        $this->quote->expects($this->exactly($calls['quote_getCurrency']))->method('getCurrency')
            ->willReturn($currency);

        $this->priceCurrencyMock->expects($this->exactly($calls['priceCurrency_format']))->method('format')
            ->willReturn($returned['priceCurrency_format']);

        $stockProductQty = 3.4;
        $this->quoteHelperMock->expects($this->exactly(1))->method('getStockForProduct')->willReturn($stockProductQty);

        $customOptions = 'custom;options;';
        $this->quoteHelperMock->expects($this->exactly(1))->method('retrieveCustomOptions')->willReturn($customOptions);

        $negotiableQuoteItemOriginalPrice = 235;
        $this->negotiableQuoteItemManagementMock->expects($this->exactly(1))->method('getOriginalPriceByItem')
            ->willReturn($negotiableQuoteItemOriginalPrice);

        $this->prepareGetItemOptionsMethod($returned, $calls);

        $negotiableQuoteShippingPrice = $negotiableQuoteShippingPrice ?: null;
        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->setMethods(['getBaseShippingAmount'])
            ->disableOriginalConstructor()->getMock();
        $addressBaseShippingAmount = 21;
        $shippingAddress->expects($this->exactly($calls['shippingAddress_getBaseShippingAmount']))
            ->method('getBaseShippingAmount')->willReturn($addressBaseShippingAmount);

        $this->quote->expects($this->exactly($calls['quote_getShippingAddress']))->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $quoteStatusLabel = 'status';
        $this->labelProviderMock->expects($this->exactly(1))->method('getLabelByStatus')->willReturn($quoteStatusLabel);

        $this->assertEquals($expected, $this->quoteUpdatesInfo->getQuoteUpdatedData($this->quote, $quoteData));
    }

    /**
     * Data provider for getQuoteUpdatedData() method.
     *
     * @return array
     */
    public function getQuoteUpdatedDataDataProvider()
    {
        $returned = [
            'priceCurrency_format' => '$234',
            'quoteItem_getMessage' => 'test Message',
            'tagFilter_filter' => 'filtered Option Value',
            'currency_getBaseToQuoteRate' => 2
        ];
        $expected = [
            'quoteId' => 35,
            'items' => [
                0 => [
                    'id' => 1,
                    'productId' => 23,
                    'name' => 'Test Product',
                    'url' => 'test/product',
                    'sku' => 'ASD2356',
                    'cost' => $returned['priceCurrency_format'],
                    'stock' => '3',
                    'qty' => 3,
                    'subtotal' => $returned['priceCurrency_format'],
                    'tax' => $returned['priceCurrency_format'],
                    'subtotalTax' => $returned['priceCurrency_format'],
                    'config' => 'custom;options;',
                    'canConfig' => true,
                    'proposedPrice' => $returned['priceCurrency_format'],
                    'cartPrice' => $returned['priceCurrency_format'],
                    'productSku' => 'ASD2356',
                    'originalPrice' => $returned['priceCurrency_format']
                ]
            ],
            'cost' => $returned['priceCurrency_format'],
            'subtotal' => $returned['priceCurrency_format'],
            'subtotalTax' => $returned['priceCurrency_format'],
            'discount' => $returned['priceCurrency_format'],
            'discountOrigin' => 30,
            'tax' => $returned['priceCurrency_format'],
            'catalogPriceValue' => 340,
            'quoteTax' => $returned['priceCurrency_format'],
            'quoteTaxAdd' => 29,
            'catalogPrice' => ['base' => $returned['priceCurrency_format']],
            'quoteSubtotal' => ['base' => $returned['priceCurrency_format']],
            'grandTotal' => [
                'base' => $returned['priceCurrency_format']
            ],
            'hasChanges' => true,
            'shippingPrice' => $returned['priceCurrency_format'],
            'quoteStatus' => 'status'
        ];

        return [
            'a' => $this->getCaseAForDataProviderGetQuoteUpdatedData($returned, $expected),
            'b' => $this->getCaseBForDataProviderGetQuoteUpdatedData($returned, $expected)
        ];
    }

    /**
     * Data provider (case A) for getQuoteUpdatedData() method.
     *
     * @param array $returned
     * @param array $expected
     * @return array
     */
    private function getCaseAForDataProviderGetQuoteUpdatedData(array $returned, array $expected)
    {
        $returned =  array_replace_recursive(
            $returned,
            [
                'productOptions_getFormattedOptionValue' => ['value' => 'value']
            ]
        );

        return [
            ['shipping' => '', 'shippingMethod' => 'shipping'],
            true, 'USD', 'USD', null, true, $returned,
            array_replace_recursive(
                $expected,
                [
                    'items' => [
                        0 => [
                            'messages' =>[
                                ['type' => 'error', 'message' => $returned['quoteItem_getMessage']]
                            ],
                            'options' => [
                                ['label' => 'Test Product', 'value' => $returned['tagFilter_filter']]
                            ]
                        ]
                    ],
                    'currencyLabel' => '',
                    'currencyRate' => 1
                ]
            ),
            [
                'tagFilter_filter' => 1,
                'priceCurrency_format' => 17,
                'quote_getCurrency' => 22,
                'quote_getShippingAddress' => 1,
                'currency_getBaseCurrencyCode' => 22,
                'currency_getQuoteCurrencyCode' => 5,
                'currency_getBaseToQuoteRate' => 0,
                'quoteItem_getBaseTaxAmount' => 2,
                'shippingAddress_getBaseShippingAmount' => 1
            ]
        ];
    }

    /**
     * Data provider (case B) for getQuoteUpdatedData() method.
     *
     * @param array $returned
     * @param array $expected
     * @return array
     */
    private function getCaseBForDataProviderGetQuoteUpdatedData(array $returned, array $expected)
    {
        $returned =  array_merge_recursive(
            $returned,
            [
                'productOptions_getFormattedOptionValue' => ['full_view' => 'full view']
            ]
        );

        return [
            ['shipping' => 24],
            false, 'USD', 'EUR', 24, false, $returned,
            array_replace_recursive(
                $expected,
                [
                    'items' => [
                        0 => [
                            'messages' =>[
                                ['type' => 'notice', 'message' => $returned['quoteItem_getMessage']]
                            ],
                            'options' => [
                                [
                                    'label' => 'Test Product',
                                    'value' => $returned['productOptions_getFormattedOptionValue']['full_view']
                                ]
                            ],
                        ]
                    ],
                    'catalogPrice' => ['quote' => $returned['priceCurrency_format']],
                    'currencyLabel' => 'USD / EUR',
                    'quoteSubtotal' => ['quote' => $returned['priceCurrency_format']],
                    'grandTotal' => ['quote' => $returned['priceCurrency_format']],
                    'currencyRate' => $returned['currency_getBaseToQuoteRate']
                ]
            ),
            [
                'tagFilter_filter' => 0,
                'priceCurrency_format' => 20,
                'quote_getCurrency' => 25,
                'quote_getShippingAddress' => 0,
                'currency_getBaseCurrencyCode' => 23,
                'currency_getQuoteCurrencyCode' => 9,
                'currency_getBaseToQuoteRate' => 2,
                'quoteItem_getBaseTaxAmount' => 1,
                'shippingAddress_getBaseShippingAmount' => 0,
            ]
        ];
    }

    /**
     * Test getMessages() method.
     * @return void
     */
    public function testGetMessages()
    {
        $message = 'test Message';
        $notifications = [$message];
        $this->messageProviderMock->expects($this->exactly(1))->method('getChangesMessages')
            ->willReturn($notifications);

        $isLockMessageDisplayed = true;
        $this->quoteHelperMock->expects($this->exactly(1))->method('isLockMessageDisplayed')
            ->willReturn($isLockMessageDisplayed);

        $lockMessage = __(
            'This quote is currently locked for editing. It will become available once released by the buyer.'
        );

        $expected = [
            [
                'type' => 'warning', 'text' => $message
            ],
            [
                'type' => 'warning', 'text' => $lockMessage
            ]
        ];
        $this->assertEquals($expected, $this->quoteUpdatesInfo->getMessages($this->quote));
    }
}
