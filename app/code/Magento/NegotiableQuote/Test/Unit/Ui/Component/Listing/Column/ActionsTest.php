<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

/**
 * Class ActionsTest
 * @package Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column
 */
class ActionsTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $className = \Magento\NegotiableQuote\Ui\Component\Listing\Column\Actions::class;

    /**
     * @param $dataSource
     * @dataProvider prepareDataSourceProvider
     */
    public function testPrepareDataSource($dataSource)
    {
        $dataSourceResult = $this->column->prepareDataSource($dataSource);

        foreach ($dataSourceResult['data']['items'] as $item) {
            $view = $item[self::COLUMN_NAME]['view'];
            $this->assertArrayHasKey('href', $view);
            $this->assertArrayHasKey('label', $view);
            $this->assertArrayHasKey('hidden', $view);
        }
    }
}
