<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\ProductItemInterface;

/**
 * SharedCatalog Page Model
 */
class ProductItem extends \Magento\Framework\Model\AbstractModel implements ProductItemInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\SharedCatalog\Model\ResourceModel\ProductItem::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::SHARED_CATALOG_PRODUCT_ITEM_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::SHARED_CATALOG_PRODUCT_ITEM_ID, $id);
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * Set Customer Group ID
     *
     * @param int $customerGroupId
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * Get Shared Catalog Product Item sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set shared Catalog Product Item sku
     *
     * @param string $sku
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }
}
