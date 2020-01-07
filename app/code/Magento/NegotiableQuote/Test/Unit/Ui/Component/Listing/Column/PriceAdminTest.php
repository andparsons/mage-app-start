<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class PriceAdminTest.
 */
class PriceAdminTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Ui\Component\Listing\Column\PriceAdmin
     */
    protected $column;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceFormatter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->priceFormatter = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $processorMock =
            $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class);
        $context->expects($this->never())->method('getProcessor')->will($this->returnValue($processorMock));
        $this->column = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Ui\Component\Listing\Column\PriceAdmin::class,
            [
                'context' => $context,
                'priceFormatter' => $this->priceFormatter,
                'storeManager' => $this->storeManager,
                'data' => [
                    'name' => 'price'
                ]
            ]
        );
    }

    /**
     * Test prepareDataSource function.
     */
    public function testPrepareDataSource()
    {
        $items = $this->getDataSourceItems();
        $expect = $this->getExpectedResult();

        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $currency->expects($this->atLeastOnce())->method('getRate')->with('EUR')->willReturn(1.5);
        $currency->expects($this->atLeastOnce())->method('getCode')->willReturn('USD');
        $this->priceFormatter->expects($this->atLeastOnce())->method('getCurrency')
            ->with(null, 'USD')->willReturn($currency);
        $this->priceFormatter->expects($this->atLeastOnce())->method('format')->willReturnArgument(0);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->atLeastOnce())->method('getCurrentCurrency')->willReturn($currency);
        $store->expects($this->atLeastOnce())->method('getBaseCurrency')->willReturn($currency);
        $store->expects($this->atLeastOnce())->method('getAvailableCurrencyCodes')->willReturn(['USD', 'EUR']);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);

        $dataSourceResult = $this->column->prepareDataSource(['data' => ['items' => $items]]);
        $this->assertEquals($expect, $dataSourceResult['data']['items']);
    }

    /**
     * Return Data source for items.
     *
     * @return array
     */
    private function getDataSourceItems()
    {
        return [
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CLOSED,
                'base_currency_code' => 'USD',
                'quote_currency_code' => 'EUR',
                'base_price' => 100,
                'price' => 150,
                'rate' => 1.5,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CLOSED,
                'base_currency_code' => 'USD',
                'quote_currency_code' => 'USD',
                'base_price' => 100,
                'price' => 150,
                'rate' => 1.5,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CREATED,
                'base_currency_code' => 'RUB',
                'quote_currency_code' => 'EUR',
                'base_price' => 100,
                'price' => 150,
                'rate' => 1.5,
                'store_id' => 1,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CREATED,
                'base_currency_code' => 'RUB',
                'quote_currency_code' => 'RUB',
                'base_price' => 100,
                'price' => 150,
                'rate' => 1.5,
                'store_id' => 1,
            ]
        ];
    }

    /**
     * Return expected array for items.
     *
     * @return array
     */
    private function getExpectedResult()
    {
        return [
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CLOSED,
                'base_currency_code' => 'USD',
                'quote_currency_code' => 'EUR',
                'base_price' => 100,
                'price' => '100 (150)',
                'rate' => 1.5,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CLOSED,
                'base_currency_code' => 'USD',
                'quote_currency_code' => 'USD',
                'base_price' => 100,
                'price' => '100',
                'rate' => 1.5,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CREATED,
                'base_currency_code' => 'RUB',
                'quote_currency_code' => 'EUR',
                'base_price' => 100,
                'price' => 100,
                'rate' => 1.5,
                'store_id' => 1,
            ],
            [
                'status_original' => NegotiableQuoteInterface::STATUS_CREATED,
                'base_currency_code' => 'RUB',
                'quote_currency_code' => 'RUB',
                'base_price' => 100,
                'price' => 100,
                'rate' => 1.5,
                'store_id' => 1,
            ]
        ];
    }
}
