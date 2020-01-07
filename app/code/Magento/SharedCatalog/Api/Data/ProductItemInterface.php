<?php
namespace Magento\SharedCatalog\Api\Data;

/**
 * ProductItemInterface interface.
 * @api
 * @since 100.0.0
 */
interface ProductItemInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SHARED_CATALOG_PRODUCT_ITEM_ID = 'entity_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const SKU = 'sku';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setId($id);

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set Customer Group ID
     *
     * @param int $customerGroupId
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Get Shared Catalog Product Item sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set shared Catalog Product Item sku
     *
     * @param string $sku
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setSku($sku);
}
