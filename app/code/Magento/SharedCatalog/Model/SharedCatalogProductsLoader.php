<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Model\ResourceModel\ProductItem as ProductItemResourceModel;

/**
 * Class for loading products for shared catalog.
 */
class SharedCatalogProductsLoader
{
    /**
     * @var ProductItemResourceModel
     */
    private $productItemResourceModel;

    /**
     * @var array
     */
    private $skuCache = [];

    /**
     * @param ProductItemResourceModel $productItemResourceModel
     */
    public function __construct(
        ProductItemResourceModel $productItemResourceModel
    ) {
        $this->productItemResourceModel = $productItemResourceModel;
    }

    /**
     * Get SKUs of products that are assigned to the shared catalog.
     *
     * @param int $customerGroupId
     * @return string[]
     */
    public function getAssignedProductsSkus($customerGroupId)
    {
        if (!isset($this->skuCache[$customerGroupId])) {
            $connection = $this->productItemResourceModel->getConnection();
            $select = $connection->select()
                ->from(['product_item' => $this->productItemResourceModel->getMainTable()], ['sku'])
                ->where(ProductItemInterface::CUSTOMER_GROUP_ID . ' = ?', $customerGroupId);
            $skuList = $connection->fetchCol($select);
            $this->skuCache[$customerGroupId] = $skuList;
        }
        return $this->skuCache[$customerGroupId];
    }

    /**
     * Get IDs of products that are assigned to the shared catalog.
     *
     * @param int $customerGroupId
     * @return int[]
     */
    public function getAssignedProductsIds($customerGroupId)
    {
        $connection = $this->productItemResourceModel->getConnection();
        $select = $connection->select()
            ->from(['product_item' => $this->productItemResourceModel->getMainTable()], [])
            ->where(ProductItemInterface::CUSTOMER_GROUP_ID . ' = ?', $customerGroupId);
        $select->joinLeft(
            ['product' => $this->productItemResourceModel->getTable('catalog_product_entity')],
            'product_item.sku = product.sku',
            ['entity_id']
        );
        $idList = $connection->fetchCol($select);

        foreach ($idList as $k => $id) {
            $idList[$k] = (int) $id;
        }

        return $idList;
    }

    /**
     * Get customer group ids that associated with products.
     *
     * @return int[]
     */
    public function getUsedCustomerGroupIds(): array
    {
        $connection = $this->productItemResourceModel->getConnection();
        $select = $connection->select()
            ->from(['product_item' => $this->productItemResourceModel->getMainTable()], ['customer_group_id'])
            ->distinct();
        $customerGroupIds = $connection->fetchCol($select);

        foreach ($customerGroupIds as $k => $id) {
            $customerGroupIds[$k] = (int) $id;
        }

        return $customerGroupIds;
    }
}
