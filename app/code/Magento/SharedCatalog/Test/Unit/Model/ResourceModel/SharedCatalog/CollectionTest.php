<?php

namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel\SharedCatalog;

/**
 * Test for Magento/SharedCatalog/Model/ResourceModel/SharedCatalog/Collection class.
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fetchStrategy;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection
     */
    private $collection;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['select', 'getConcatSql', 'prepareSqlCondition'])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getMainTable', 'getTable'])
            ->getMockForAbstractClass();
        $this->select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection->expects($this->exactly(2))->method('select')->willReturn($this->select);
        $this->resource->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->resource->expects($this->once())->method('getMainTable')
            ->willReturn('shared_catalog');
        $this->resource->expects($this->exactly(2))->method('getTable')
            ->willReturn('shared_catalog');
        $this->entityFactory = $this->getMockBuilder(\Magento\Framework\Data\Collection\EntityFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fetchStrategy
            = $this->getMockBuilder(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collection = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class,
            [
                'entityFactory' => $this->entityFactory,
                'logger' => $this->logger,
                'fetchStrategy' => $this->fetchStrategy,
                'eventManager' => $this->eventManager,
                'storeManager' => $this->storeManager,
                'connection' => $this->connection,
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * Test for \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::addFieldToFilter.
     *
     * @return void
     */
    public function testAddFieldToFilter()
    {
        $field = 'admin_user';
        $condition = ['like' => '%admin%'];
        $fieldSql = 'CONCAT_WS(\' \', \'customer_entity.firstname\', \'customer_entity.lastname\'';
        $conditionSql = $fieldSql . ' like \'%admin%\'';
        $result = '';
        $whereCallback = function ($resultCondition) use (&$result) {
            $result = $resultCondition;
        };
        $this->connection->expects($this->once())->method('getConcatSql')->willReturn($fieldSql);
        $this->connection->expects($this->once())->method('prepareSqlCondition')->willReturn($conditionSql);
        $this->select->expects($this->any())->method('where')->will($this->returnCallback($whereCallback));
        $this->collection->addFieldToFilter($field, $condition);
        $this->assertEquals($conditionSql, $result);
    }
}
