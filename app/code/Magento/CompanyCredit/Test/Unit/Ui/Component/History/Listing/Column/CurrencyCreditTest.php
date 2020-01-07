<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History\Listing\Column;

/**
 * Class CurrencyCreditTest.
 */
class CurrencyCreditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\Listing\Column\CurrencyCredit
     */
    private $currencyCreditColumn;

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
        $this->currencyCreditColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\Listing\Column\CurrencyCredit::class,
            [
                'context' => $context,
                'priceFormatter' => $this->priceFormatter,
                'websiteCurrency' => $this->websiteCurrency,
            ]
        );
        $this->currencyCreditColumn->setData('name', 'balance');
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSource()
    {
        $currencyCode = 'EUR';
        $dataSource = [
            'data' => [
                'items' => [
                    ['balance' => 100, 'currency_credit' => $currencyCode],
                    ['balance' => 200],
                ]
            ]
        ];

        $expected = [
            'data' => [
                'items' => [
                    ['balance' => '€100', 'currency_credit' => $currencyCode],
                    ['balance' => '$200'],
                ]
            ]
        ];

        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $baseCurrency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->websiteCurrency->expects($this->exactly(2))->method('getCurrencyByCode')
            ->withConsecutive([$currencyCode], [null])
            ->willReturnOnConsecutiveCalls($currency, $baseCurrency);
        $this->priceFormatter->expects($this->at(0))->method('format')
            ->with(
                100,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $currency
            )->willReturn('€100');
        $this->priceFormatter->expects($this->at(1))->method('format')
            ->with(
                200,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $baseCurrency
            )->willReturn('$200');

        $this->assertEquals($expected, $this->currencyCreditColumn->prepareDataSource($dataSource));
    }
}
