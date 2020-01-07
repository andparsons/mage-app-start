<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\Company\Listing\Column;

/**
 * Class AmountCurrencyTest.
 */
class AmountCurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatter;

    /**
     * @var \Magento\CompanyCredit\Ui\Component\Company\Listing\Column\AmountCurrency
     */
    private $companyCreditColumn;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->priceFormatter = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $processor = $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class);
        $context->expects($this->never())->method('getProcessor')->willReturn($processor);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyCreditColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\Company\Listing\Column\AmountCurrency::class,
            [
                'context' => $context,
                'priceFormatter' => $this->priceFormatter,
                '_data' => ['name' => 'balance']
            ]
        );
    }

    /**
     * Test method for prepareDataSource.
     *
     * @param array $dataSource
     * @param array $expected
     * @return void
     * @dataProvider prepareDataSourceDataProvider
     */
    public function testPrepareDataSource(array $dataSource, array $expected)
    {
        $i = 0;
        foreach ($dataSource as $item) {
            $this->priceFormatter->expects($this->at($i))->method('format')
                ->with($item['items'][$i]['balance'], false)
                ->willReturn($expected['data']['items'][$i]['balance']);
            $i++;
            if ($item['items'][$i]['balance'] != 0) {
                $this->priceFormatter->expects($this->at($i))->method('format')
                    ->with($item['items'][$i]['balance'], false)
                    ->willReturn($expected['data']['items'][$i]['balance']);
            }
        }

        $this->assertEquals($expected, $this->companyCreditColumn->prepareDataSource($dataSource));
    }

    /**
     * Data provider for prepareDataSource method.
     *
     * @return array
     */
    public function prepareDataSourceDataProvider()
    {
        return [
            [
                [
                    'data' => [
                        'items' => [
                            ['balance' => 100, 'currency_credit' => 'null'],
                            ['balance' => 200],
                        ]
                    ]
                ],
                [
                    'data' => [
                        'items' => [
                            ['balance' => '$100', 'currency_credit' => 'null'],
                            ['balance' => '$200'],
                        ]
                    ]
                ]
            ],
            [
                [
                    'data' => [
                        'items' => [
                            ['balance' => 100, 'currency_credit' => 'null'],
                            ['balance' => 0],
                        ]
                    ]
                ],
                [
                    'data' => [
                        'items' => [
                            ['balance' => '$100', 'currency_credit' => 'null'],
                            ['balance' => null],
                        ]
                    ]
                ]
            ]
        ];
    }
}
