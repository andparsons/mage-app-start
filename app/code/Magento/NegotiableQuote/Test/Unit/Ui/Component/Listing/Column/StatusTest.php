<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class StatusTest
 * @package Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column
 */
class StatusTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $className = \Magento\NegotiableQuote\Ui\Component\Listing\Column\Status::class;

    /**
     * Prepare set up arguments
     *
     * @param array $arguments
     * @return array
     */
    protected function setUpPrepareArguments(array $arguments)
    {
        $arguments['labelProvider'] = new \Magento\NegotiableQuote\Model\Status\BackendLabelProvider();
        return parent::setUpPrepareArguments($arguments);
    }

    /**
     * @param $dataSource
     * @param string $label
     * @dataProvider prepareDataSourceProvider
     */
    public function testPrepareDataSource($dataSource, $label)
    {
        $dataSourceResult = $this->column->prepareDataSource(['data' => $dataSource]);
        foreach ($dataSourceResult['data']['items'] as $item) {
            $view = $item[self::COLUMN_NAME];
            $this->assertEquals($label, $view);
        }
    }

    /**
     * Data provider data source
     * @return array
     */
    public function prepareDataSourceProvider()
    {
        return [
            [
                [
                    'items' => [
                        // item 1
                        [
                            'entity_id' => 1,
                            self::COLUMN_NAME => NegotiableQuoteInterface::STATUS_CREATED
                        ]
                    ]
                ],
                'New'
            ],
            [
                [
                    'items' => [
                        // item 1
                        [
                            'entity_id' => 1,
                            self::COLUMN_NAME => NegotiableQuoteInterface::STATUS_CLOSED
                        ]
                    ]
                ],
                'Closed'
            ]
        ];
    }
}
