<?php

namespace Magento\SharedCatalog\Model\ResourceModel;

/**
 * ProductItem mysql resource.
 */
class ProductItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'shared_catalog_product_item',
            'entity_id'
        );
    }

    /**
     * Create product links in bulk.
     *
     * @param array $skus
     * @param int $customerGroupId
     * @return int Number of added items
     */
    public function createItems(array $skus, $customerGroupId)
    {
        $data = [];
        foreach ($skus as $sku) {
            $data[] = ['sku' => $sku, 'customer_group_id' => $customerGroupId];
        }

        return $this->getConnection()->insertMultiple(
            $this->getTable('shared_catalog_product_item'),
            $data
        );
    }

    /**
     * Delete product links by SKUs in bulk.
     *
     * @param array $skus
     * @param int $customerGroupId
     * @return void
     */
    public function deleteItems(array $skus, $customerGroupId)
    {
        $tableName = $this
            ->getTable('shared_catalog_product_item');
        $select = $this->getConnection()->select()
            ->from($tableName)
            ->where('sku IN (?)', $skus)
            ->where('customer_group_id = ?', $customerGroupId);

        $this->getConnection()->query($this->getConnection()->deleteFromSelect($select, $tableName));
    }
}
