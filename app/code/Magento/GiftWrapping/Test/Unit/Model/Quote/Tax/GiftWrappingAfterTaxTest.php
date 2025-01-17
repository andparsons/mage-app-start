<?php
namespace Magento\GiftWrapping\Test\Unit\Model\Quote\Tax;

use Magento\Quote\Model\Quote\Address;

/**
 * Test class for \Magento\GiftWrapping\Model\Quote\Tax\GiftwrappingAfterTax
 */
class GiftWrappingAfterTaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\GiftWrapping\Model\Wrapping
     */
    protected $wrappingMock;

    protected function setUp()
    {
        $this->wrappingMock = $this->createPartialMock(
            \Magento\GiftWrapping\Model\Wrapping::class,
            ['load', 'setStoreId', 'getBasePrice', '__wakeup']
        );
    }

    /**
     * Test for collect method
     *
     * @dataProvider collectQuoteDataProvider
     * @param array $extraTaxableDetails
     * @param array $expectedGwItems
     * @param array $expectedGw
     * @param array $expectedGwCard
     */
    public function testCollectQuote(
        $extraTaxableDetails,
        $expectedGwItems,
        $expectedGw,
        $expectedGwCard
    ) {
        $helperMock = $this->createMock(\Magento\GiftWrapping\Helper\Data::class);
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['isVirtual', '__wakeup']);
        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['convertPrice', 'getId', '__wakeup']);

        $addressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            [
                'getAddressType',
                '__wakeup',
                'getExtraTaxableDetails',
            ]
        );

        $item = new \Magento\Framework\DataObject();
        $storeMock->expects($this->any())->method('convertPrice')->willReturn(10);
        $product->expects($this->any())->method('isVirtual')->willReturn(false);

        $quoteData = [
            'isMultishipping' => false,
            'store' => $storeMock,
            'billingAddress' => null,
            'customerTaxClassId' => null
        ];
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->setData($quoteData);

        $this->wrappingMock->expects($this->any())->method('load')->willReturnSelf();
        $this->wrappingMock->expects($this->any())->method('getBasePrice')->willReturn(6);

        $product->setGiftWrappingPrice(10);
        $item->setProduct($product)->setQty(2)->setGwId(1)->setGwPrice(5)->setGwBasePrice(10);
        $addressMock->expects($this->any())->method('getAddressType')->willReturn(Address::TYPE_SHIPPING);

        $shippingMock = $this->createMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shippingMock->expects($this->atLeastOnce())->method('getAddress')->willReturn($addressMock);
        $shippingAssignmentMock = $this->createMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $shippingAssignmentMock->expects($this->atLeastOnce())->method('getShipping')->willReturn($shippingMock);
        $totalMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address\Total::class,
            ['getExtraTaxableDetails', 'getGwItemCodeToItemMapping']
        );
        $totalMock->expects($this->atLeastOnce())->method('getExtraTaxableDetails')->willReturn($extraTaxableDetails);
        $totalMock->expects($this->any())->method('getGwItemCodeToItemMapping')->willReturn(['testCode' => $item]);

        $model = new \Magento\GiftWrapping\Model\Total\Quote\Tax\GiftwrappingAfterTax($helperMock);
        $model->collect($quote, $shippingAssignmentMock, $totalMock);

        $this->assertEquals($expectedGwItems['gw_items_base_tax_amount'], $totalMock->getGwItemsBaseTaxAmount());
        $this->assertEquals($expectedGwItems['gw_items_tax_amount'], $totalMock->getGwItemsTaxAmount());
        $this->assertEquals($expectedGwItems['gw_items_price_incl_tax'], $totalMock->getGwItemsPriceInclTax());
        $this->assertEquals($expectedGwItems['gw_items_base_price_incl_tax'], $totalMock->getGwItemsBasePriceInclTax());
        $this->assertEquals($expectedGw['gw_base_tax_amount'], $totalMock->getGwBaseTaxAmount());
        $this->assertEquals($expectedGw['gw_tax_amount'], $totalMock->getGwTaxAmount());
        $this->assertEquals($expectedGw['gw_price_incl_tax'], $totalMock->getGwPriceInclTax());
        $this->assertEquals($expectedGw['gw_base_price_incl_tax'], $totalMock->getGwBasePriceInclTax());
        $this->assertEquals($expectedGwCard['gw_card_base_tax_amount'], $totalMock->getGwCardBaseTaxAmount());
        $this->assertEquals($expectedGwCard['gw_card_tax_amount'], $totalMock->getGwCardTaxAmount());
        $this->assertEquals($expectedGwCard['gw_card_price_incl_tax'], $totalMock->getGwCardPriceInclTax());
        $this->assertEquals($expectedGwCard['gw_card_base_price_incl_tax'], $totalMock->getGwCardBasePriceInclTax());
    }

    /**
     * @return array
     */
    public function collectQuoteDataProvider()
    {
        $quote = [
            'quote' => [
                [
                    'code' => 'testCode',
                    'base_row_tax' => 100,
                    'row_tax' => 200,
                    'price_incl_tax' => 300,
                    'base_price_incl_tax' => 400
                ]
            ]
        ];
        $gwWithItemQuotePrintedCard = [
            'item_gw' => $quote, 'quote_gw' => $quote, 'printed_card_gw' => $quote
        ];
        $gwWithItem = [
            'item_gw' => $quote
        ];
        $gwWithQuote = [
            'quote_gw' => $quote
        ];
        $gwWithPrintedCard = [
            'printed_card_gw' => $quote
        ];

        $expectedGwDataSet = [
            'gw_base_tax_amount' => 100,
            'gw_tax_amount' => 200,
            'gw_price_incl_tax' => 300,
            'gw_base_price_incl_tax' => 400
        ];
        $expectedGwDataUnset = [
            'gw_base_tax_amount' => false,
            'gw_tax_amount' => false,
            'gw_price_incl_tax' => false,
            'gw_base_price_incl_tax' => false
        ];
        $expectedGwItemsDataSet = [
            'gw_items_base_tax_amount' => 100,
            'gw_items_tax_amount' => 200,
            'gw_items_price_incl_tax' => 300,
            'gw_items_base_price_incl_tax' => 400
        ];
        $expectedGwItemsDataUnset = [
            'gw_items_base_tax_amount' => false,
            'gw_items_tax_amount' => false,
            'gw_items_price_incl_tax' => false,
            'gw_items_base_price_incl_tax' => false
        ];
        $expectedGwCardDataSet = [
            'gw_card_base_tax_amount' => 100,
            'gw_card_tax_amount' => 200,
            'gw_card_price_incl_tax' => 300,
            'gw_card_base_price_incl_tax' => 400
        ];
        $expectedGwCardDataUnset = [
            'gw_card_base_tax_amount' => false,
            'gw_card_tax_amount' => false,
            'gw_card_price_incl_tax' => false,
            'gw_card_base_price_incl_tax' => false
        ];

        return [
            [
                $gwWithItemQuotePrintedCard, $expectedGwItemsDataSet, $expectedGwDataSet, $expectedGwCardDataSet
            ],
            [
                $gwWithItem, $expectedGwItemsDataSet, $expectedGwDataUnset, $expectedGwCardDataUnset
            ],
            [
                $gwWithQuote, $expectedGwItemsDataUnset, $expectedGwDataSet, $expectedGwCardDataUnset
            ],
            [
                $gwWithPrintedCard, $expectedGwItemsDataUnset, $expectedGwDataUnset, $expectedGwCardDataSet
            ],
            [
                [], $expectedGwItemsDataUnset, $expectedGwDataUnset, $expectedGwCardDataUnset
            ],
            [
                null, $expectedGwItemsDataUnset, $expectedGwDataUnset, $expectedGwCardDataUnset
            ],
        ];
    }
}
