<?php

namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel;

/**
 * Unit test for ProductItem resource model.
 */
class ProductItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resources;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem
     */
    private $productItem;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resources = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->productItem = $objectManager->getObject(
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem::class,
            [
                '_resources' => $this->resources,
            ]
        );
    }

    /**
     * Test for createItems method.
     *
     * @return void
     */
    public function testCreateItems()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resources->expects($this->once())->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())->method('getTableName')
            ->with('shared_catalog_product_item', 'default')
            ->willReturn('shared_catalog_product_item');
        $connection->expects($this->once())->method('insertMultiple')
            ->with(
                'shared_catalog_product_item',
                [
                    ['sku' => $productSkus[0], 'customer_group_id' => $customerGroupId],
                    ['sku' => $productSkus[1], 'customer_group_id' => $customerGroupId]
                ]
            )->willReturn(2);
        $this->productItem->createItems($productSkus, $customerGroupId);
    }

    /**
     * Test for deleteItems method.
     *
     * @return void
     */
    public function testDeleteItems()
    {
        $customerGroupId = 1;
        $productSkus = ['SKU1', 'SKU2'];
        $tableName = 'shared_catalog_product_item';
        $deleteQuery = 'DELETE FROM...';
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->resources->expects($this->atLeastOnce())
            ->method('getConnection')->with('default')->willReturn($connection);
        $this->resources->expects($this->once())
            ->method('getTableName')->with($tableName, 'default')->willReturn($tableName);
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with($tableName)->willReturnSelf();
        $select->expects($this->exactly(2))->method('where')->withConsecutive(
            ['sku IN (?)', $productSkus],
            ['customer_group_id = ?', $customerGroupId]
        )->willReturnSelf();
        $connection->expects($this->once())
            ->method('deleteFromSelect')->with($select, $tableName)->willReturn($deleteQuery);
        $dbStatement = $this->getMockBuilder(\Zend_Db_Statement_Interface::class)
            ->disableOriginalConstructor()->getMock();
        $connection->expects($this->once())
            ->method('query')->with($deleteQuery)->willReturn($dbStatement);
        $this->productItem->deleteItems($productSkus, $customerGroupId);
    }
}
