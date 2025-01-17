<?php

namespace Magento\CatalogInventory\Test\Unit\Model\Source;

use PHPUnit\Framework\TestCase;

class StockTest extends TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Source\Stock
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\CatalogInventory\Model\Source\Stock();
    }

    public function testAddValueSortToCollection()
    {
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $collectionMock = $this->createMock(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class);
        $collectionMock->expects($this->atLeastOnce())->method('getSelect')->willReturn($selectMock);

        $selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['stock_item_table' => 'cataloginventory_stock_item'],
                "e.entity_id=stock_item_table.product_id",
                []
            )
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('order')
            ->with("stock_item_table.qty DESC")
            ->willReturnSelf();

        $this->model->addValueSortToCollection($collectionMock);
    }
}
