<?php
namespace Magento\CatalogInventory\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Model\ResourceModel\Product\StockStatusBaseSelectProcessor;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class StockStatusBaseSelectProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var StockStatusBaseSelectProcessor
     */
    private $stockStatusBaseSelectProcessor;

    protected function setUp()
    {
        $this->resource = $this->getMockBuilder(ResourceConnection::class)->disableOriginalConstructor()->getMock();
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->stockStatusBaseSelectProcessor =  (new ObjectManager($this))->getObject(
            StockStatusBaseSelectProcessor::class,
            [
                'resource' => $this->resource
            ]
        );
    }

    public function testProcess()
    {
        $tableName = 'table_name';

        $this->resource->expects($this->once())->method('getTableName')->willReturn($tableName);

        $this->select->expects($this->once())
            ->method('join')
            ->with(
                ['stock' => $tableName],
                sprintf('stock.product_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            )
            ->willReturnSelf();

        $this->select->expects($this->exactly(2))
            ->method('where')
            ->withConsecutive(
                ['stock.stock_status = ?', Stock::STOCK_IN_STOCK, null],
                ['stock.website_id = ?', 0, null]
            )
            ->willReturnSelf();

        $this->stockStatusBaseSelectProcessor->process($this->select);
    }
}
