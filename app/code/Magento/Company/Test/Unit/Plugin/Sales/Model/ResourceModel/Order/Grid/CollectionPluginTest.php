<?php
declare(strict_types=1);

namespace Magento\Company\Test\Unit\Plugin\Sales\Model\ResourceModel\Order\Grid;

/**
 * Unit tests for \Magento\Company\Plugin\Sales\Model\ResourceModel\Order\Grid\CollectionPlugin.
 */
class CollectionPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Plugin\Sales\Model\ResourceModel\Order\Grid\CollectionPlugin
     */
    private $collectionPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collectionPlugin = $objectManager->getObject(
            \Magento\Company\Plugin\Sales\Model\ResourceModel\Order\Grid\CollectionPlugin::class
        );
    }

    public function testBeforeLoad()
    {
        $orderEntityTable = 'order_entity_table';
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods(['joinLeft'])
            ->getMock();
        $select->expects($this->atLeastOnce())->method('joinLeft')->with(
            ['company_order' => $orderEntityTable],
            'main_table.entity_id = company_order.order_id',
            ['company_name']
        )->willReturnSelf();
        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['isLoaded', 'getSelect', 'getTable'])
            ->getMock();
        $collection->expects($this->atLeastOnce())->method('isLoaded')->willReturn(false);
        $collection->expects($this->atLeastOnce())->method('getSelect')->willReturn($select);
        $collection->expects($this->atLeastOnce())->method('getTable')
            ->with('company_order_entity')
            ->willReturn($orderEntityTable);

        $this->assertEquals([false, false], $this->collectionPlugin->beforeLoad($collection));
    }

    public function testBeforeLoadWithLoadedCollection()
    {
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->never())->method('joinLeft');
        $collection = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->atLeastOnce())->method('isLoaded')->willReturn(true);
        $collection->expects($this->never())->method('getSelect');
        $collection->expects($this->never())->method('getTable');

        $this->assertEquals([false, false], $this->collectionPlugin->beforeLoad($collection));
    }
}
