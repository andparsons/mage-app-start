<?php

namespace Magento\SharedCatalog\Model\ResourceModel;

/**
 * Resource model for Shared catalog Permission table.
 */
class Permission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'sharedcatalog_category_permissions',
            'permission_id'
        );
    }

    /**
     * Initialize unique scope for shared catalog permission.
     *
     * @return void
     */
    protected function _initUniqueFields()
    {
        parent::_initUniqueFields();
        $this->_uniqueFields[] = [
            'field' => ['category_id', 'website_id', 'customer_group_id'],
            'title' => __('Permission with the same scope'),
        ];
    }

    /**
     * Delete Shared Catalog category permissions connected with the category.
     *
     * @param int $categoryId
     * @return void
     */
    public function deleteItems(int $categoryId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['category_id = ?' => $categoryId]);
    }

    /**
     * Get array of categories ids with specified permissions for provided customer group id.
     *
     * @param int $customerGroupId
     * @param int $permission
     * @return int[]
     */
    public function getCategoriesWithPermission(int $customerGroupId, int $permission): array
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), ['category_id'])
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('permission = ?', $permission)
            ->distinct();
        $categoryIds = array_map(
            function ($value) {
                return (int) $value;
            },
            $connection->fetchCol($select)
        );

        return $categoryIds;
    }

    /**
     * Get permission.
     *
     * @param int $categoryId
     * @param int|null $websiteId
     * @param int|null $groupId
     * @return array|null
     */
    public function getPermission(int $categoryId, ?int $websiteId, ?int $groupId): ?array
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable());
        $select->where('category_id = ?', $categoryId);
        $select->where(
            $connection->prepareSqlCondition('customer_group_id', ['seq' => $groupId])
        );
        $websiteWhere = $connection->prepareSqlCondition('website_id', ['null' => null]);
        if ($websiteId) {
            $websiteWhere .= ' OR ' . $connection->prepareSqlCondition('website_id', ['eq' => $websiteId]);
        }
        $select->where('(' . $websiteWhere . ')');
        $select->order(new \Zend_Db_Expr('website_id IS NULL ASC'));
        $select->limit(1);
        $row = $connection->fetchRow($select);

        return $row ?: null;
    }
}
