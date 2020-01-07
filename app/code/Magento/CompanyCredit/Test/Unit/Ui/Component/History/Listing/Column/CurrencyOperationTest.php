<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History\Listing\Column;

/**
 * Class CurrencyOperationTest.
 */
class CurrencyOperationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\Listing\Column\CurrencyOperation
     */
    private $currencyOperationColumn;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatter;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrency;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->priceFormatter = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->websiteCurrency = $this->createMock(
            \Magento\CompanyCredit\Model\WebsiteCurrency::class
        );
        $context = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class
        );
        $processor = $this->createMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class
        );
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->currencyOperationColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\Listing\Column\CurrencyOperation::class,
            [
                'context' => $context,
                'priceFormatter' => $this->priceFormatter,
                'websiteCurrency' => $this->websiteCurrency,
            ]
        );
        $this->currencyOperationColumn->setData('name', 'balance');
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSource()
    {
        $creditCurrencyCode = 'USD';
        $operationCurrencyCode = 'EUR';
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'balance' => 100,
                        'currency_operation' => $operationCurrencyCode,
                        'currency_credit' => $creditCurrencyCode,
                        'rate' => 1.2,
                        'rate_credit' => 1.5,
                        'type' => 3
                    ],
                    ['balance' => 200, 'type' => 3],
                    ['balance' => 300, 'type' => 1],
                    ['balance' => 400, 'type' => 2],
                ]
            ]
        ];

        $expected = [
            'data' => [
                'items' => [
                    [
                        'balance' => '€150 ($120)<br>USD/EUR: 0.8000',
                        'currency_operation' => $operationCurrencyCode,
                        'currency_credit' => $creditCurrencyCode,
                        'rate' => 1.2,
                        'rate_credit' => 1.5,
                        'type' => 3,
                        'balance_original' => 100
                    ],
                    ['balance' => '$200', 'type' => 3, 'balance_original' => 200],
                    ['balance' => '', 'type' => 1, 'balance_original' => 300],
                    ['balance' => '', 'type' => 2, 'balance_original' => 400],
                ]
            ]
        ];

        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $operationCurrency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->websiteCurrency->expects($this->exactly(3))->method('getCurrencyByCode')
            ->withConsecutive([$creditCurrencyCode], [$operationCurrencyCode], [null])
            ->willReturnOnConsecutiveCalls($currency, $operationCurrency, $currency);
        $this->priceFormatter->expects($this->at(0))->method('format')
            ->with(
                150,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $currency
            )->willReturn('€150');
        $this->priceFormatter->expects($this->at(1))->method('format')
            ->with(
                120,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $operationCurrency
            )->willReturn('$120');
        $this->priceFormatter->expects($this->at(2))->method('format')
            ->with(
                200,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $currency
            )->willReturn('$200');

        $this->assertEquals($expected, $this->currencyOperationColumn->prepareDataSource($dataSource));
    }
}
