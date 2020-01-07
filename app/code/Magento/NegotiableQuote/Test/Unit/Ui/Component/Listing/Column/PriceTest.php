<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for Price.
 */
class PriceTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $className = \Magento\NegotiableQuote\Ui\Component\Listing\Column\Price::class;

    /**
     * {@inheritdoc}
     */
    protected function setUpPrepareArguments(array $arguments)
    {
        $arguments = parent::setUpPrepareArguments($arguments);

        $currentCurrency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseCurrency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentCurrency', 'getBaseCurrency'])
            ->getMockForAbstractClass();
        $storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getCurrentCurrency')->willReturn($currentCurrency);
        $store->expects($this->exactly(2))->method('getBaseCurrency')->willReturn($baseCurrency);
        $currentCurrency->expects($this->exactly(2))->method('getCode')->willReturn('USD');
        $baseCurrency->expects($this->once())->method('getCode')->willReturn('EUR');
        $baseCurrency->expects($this->once())->method('getRate')->with('USD')->willReturn(1.5);
        $arguments['storeManager'] = $storeManager;
        $priceFormatter = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $priceFormatter->expects($this->once())->method('getCurrency')->willReturn($currentCurrency);
        $priceFormatter->expects($this->once())->method('convert')->with(20)->willReturn(30);
        $priceFormatter->expects($this->exactly(3))->method('format')
            ->withConsecutive(
                [10, false, 2, null, 'EUR'],
                [50, false, 2, null, 'USD'],
                [30, false, 2, null, 'USD']
            )
            ->willReturnOnConsecutiveCalls('€10.00', '$50.00', '$10.00');
        $arguments['priceFormatter'] = $priceFormatter;
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serializer->expects($this->once())->method('unserialize')->with(
            json_encode(['quote' => ['grand_total' => 10.00, 'quote_currency_code' => 'EUR']])
        )->willReturn(['quote' => ['grand_total' => 10.00, 'quote_currency_code' => 'EUR']]);
        $arguments['serializer'] = $serializer;

        return $arguments;
    }

    /**
     * Test for prepareDataSource method.
     *
     * @param array $dataSource
     * @return void
     * @dataProvider prepareDataSourceProvider
     */
    public function testPrepareDataSource(array $dataSource)
    {
        $dataSourceResult = $this->column->prepareDataSource($dataSource);

        foreach ($dataSourceResult['data']['items'] as $item) {
            $this->assertArrayHasKey(self::COLUMN_NAME, $item);
        }
    }

    /**
     * Data provider data source.
     *
     * @return array
     */
    public function prepareDataSourceProvider()
    {
        return [
            [
                [
                    'data' => [
                        'items' => [
                            // item 1
                            [
                                'entity_id' => 1,
                                self::COLUMN_NAME => 3,
                                NegotiableQuoteInterface::SHIPPING_PRICE => 2,
                                'status' => NegotiableQuoteInterface::STATUS_ORDERED,
                                'snapshot' => json_encode(
                                    [
                                        'quote' => [
                                            'grand_total' => 10.00,
                                            'quote_currency_code' => 'EUR'
                                        ]
                                    ]
                                ),
                                'customer_id' => 4,
                                'grand_total' => 50,
                                'quote_currency_code' => 'USD',
                            ],
                            // item 2
                            [
                                'entity_id' => 1,
                                self::COLUMN_NAME => 3,
                                NegotiableQuoteInterface::SHIPPING_PRICE => 2,
                                'customer_id' => 4,
                                'status' => NegotiableQuoteInterface::STATUS_CREATED,
                                'grand_total' => 50,
                                'quote_currency_code' => 'USD',
                            ],
                            // item 3
                            [
                                'entity_id' => 2,
                                self::COLUMN_NAME => 20,
                                'customer_id' => null,
                                'status' => NegotiableQuoteInterface::STATUS_CREATED,
                                'store_id' => 1,
                                'quote_currency_code' => 'USD',
                                'base_currency_code' => 'EUR',
                                'base_to_quote_rate' => 1.4,
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
