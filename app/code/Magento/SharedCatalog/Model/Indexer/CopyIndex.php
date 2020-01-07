<?php
namespace Magento\SharedCatalog\Model\Indexer;

use Magento\Customer\Api\Data\GroupInterface;

/**
 * Copy index data in the table from default customer group
 */
class CopyIndex
{
    /**
     * Copy rows batch size
     */
    const COPY_ROWS_BATCH_SIZE = 50000;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Query\BatchIteratorFactory
     */
    private $batchIteratorFactory;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\DB\Query\BatchRangeIteratorFactory $batchIteratorFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Query\BatchRangeIteratorFactory $batchIteratorFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->batchIteratorFactory = $batchIteratorFactory;
        $this->groupManagement = $groupManagement;
    }

    /**
     * Copy index in the tables from default customer group
     * @param GroupInterface $group
     * @param array $tables
     * @param array $defaultTables
     */
    public function copy(GroupInterface $group, array $tables, array $defaultTables = null)
    {
        array_map(function ($target, $source) use ($group) {
            $this->copyIndexForTable($group->getId(), $target, $source);
        }, $tables, $defaultTables ?? $tables);
    }

    /**
     * Copy index for table
     * @param int $newGroupId
     * @param string $indexTableName
     * @param $defaultTable
     * @return void
     */
    private function copyIndexForTable($newGroupId, $indexTableName, $defaultTable)
    {
        $indexTableName = $this->resourceConnection->getTableName($indexTableName);

        $tableData = $this->resourceConnection->getConnection()->describeTable($indexTableName);
        $indexes = $this->resourceConnection->getConnection()->getIndexList($indexTableName);

        $rangeField = 'customer_group_id';
        if (isset($indexes['PRIMARY']['COLUMNS_LIST'])) {
            $rangeField = $indexes['PRIMARY']['COLUMNS_LIST'];
        }

        $columns = array_keys($tableData);
        $customerGroupIdColumnIndex = array_search('customer_group_id', $columns);

        $columns[$customerGroupIdColumnIndex] = new \Zend_Db_Expr($newGroupId . ' AS customer_group_id');

        $customerGroupPricesSelect = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                ['index' => $this->resourceConnection->getTableName($defaultTable)],
                $columns
            )
            ->where(
                'customer_group_id = ?',
                $this->groupManagement->getDefaultGroup()->getId()
            );

        $batchIterator = $this->batchIteratorFactory->create(
            [
                'batchSize' => self::COPY_ROWS_BATCH_SIZE,
                'select' => $customerGroupPricesSelect,
                'correlationName' => 'index',
                'rangeField' => $rangeField,
                'rangeFieldAlias' => '',
            ]
        );

        foreach ($batchIterator as $select) {
            $query = $this->resourceConnection->getConnection()->insertFromSelect(
                $select,
                $indexTableName
            );
            $this->resourceConnection->getConnection()->query($query);
        }
    }
}
