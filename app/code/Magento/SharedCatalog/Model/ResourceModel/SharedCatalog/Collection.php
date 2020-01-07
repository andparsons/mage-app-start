<?php
namespace Magento\SharedCatalog\Model\ResourceModel\SharedCatalog;

/**
 * Shared catalog collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $columnNameAdminUser = 'admin_user';

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\SharedCatalog\Model\SharedCatalog::class,
            \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog::class
        );
        $this->getSelect();
    }

    /**
     * Join customer group table.
     *
     * @return \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection
     */
    private function joinCustomerGroupTable()
    {
        $this->getSelect()->join(
            ['customer_group' => $this->getTable('customer_group')],
            'main_table.customer_group_id = customer_group.customer_group_id',
            [
                'customer_group_code' => 'customer_group.customer_group_code',
                'tax_class_id' => 'customer_group.tax_class_id'
            ]
        );

        return $this;
    }

    /**
     * Join customer table.
     *
     * @return \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection
     */
    public function joinCustomerTable()
    {
        $this->getSelect()->joinLeft(
            ['customer_entity' => $this->getTable('admin_user')],
            'main_table.created_by = customer_entity.user_id',
            [
                $this->columnNameAdminUser => $this->getConnection()->getConcatSql(
                    ['customer_entity.firstname', 'customer_entity.lastname'],
                    ' '
                )
            ]
        );

        return $this;
    }

    /**
     * Add field filter to collection.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == $this->columnNameAdminUser) {
            $field = $this->getConnection()->getConcatSql(
                ['customer_entity.firstname', 'customer_entity.lastname'],
                ' '
            );
            $resultCondition = $this->getConnection()->prepareSqlCondition($field, $condition);
            $this->getSelect()->where($resultCondition);
        } else {
            parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinCustomerGroupTable();
        $this->addFilterToMap('customer_group_id', 'main_table.customer_group_id');

        return $this;
    }
}
