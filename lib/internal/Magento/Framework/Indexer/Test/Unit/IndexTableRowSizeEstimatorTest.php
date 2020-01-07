<?php

namespace Magento\Framework\Indexer\Test\Unit;

class IndexTableRowSizeEstimatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test for estimateRowSize method
     */
    public function testEstimateRowSize()
    {
        $rowMemorySize = 100;
        $model = new \Magento\Framework\Indexer\IndexTableRowSizeEstimator($rowMemorySize);
        $this->assertEquals($model->estimateRowSize(), $rowMemorySize);
    }
}
