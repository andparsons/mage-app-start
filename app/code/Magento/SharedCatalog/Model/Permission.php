<?php

namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\PermissionInterface;

/**
 * Shared Catalog Permission model for displaying category permissions in categories tree.
 */
class Permission extends \Magento\Framework\Model\AbstractModel implements PermissionInterface
{
    /**
     * Initialize model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\SharedCatalog\Model\ResourceModel\Permission::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::SHARED_CATALOG_PERMISSION_ID) === null
            ? null
            : (int) $this->getData(self::SHARED_CATALOG_PERMISSION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::SHARED_CATALOG_PERMISSION_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::SHARED_CATALOG_PERMISSION_CATEGORY_ID) === null
            ? null
            : (int) $this->getData(self::SHARED_CATALOG_PERMISSION_CATEGORY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCategoryId($value)
    {
        $this->setData(self::SHARED_CATALOG_PERMISSION_CATEGORY_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return $this->getData(self::SHARED_CATALOG_PERMISSION_WEBSITE_ID) === null
            ? null
            : (int) $this->getData(self::SHARED_CATALOG_PERMISSION_WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($value)
    {
        $this->setData(self::SHARED_CATALOG_PERMISSION_WEBSITE_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId()
    {
        return $this->getData(self::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID) === null
            ? null
            : (int) $this->getData(self::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupId($value)
    {
        $this->setData(self::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID, $value);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPermission()
    {
        return $this->getData(self::SHARED_CATALOG_PERMISSION_PERMISSION) === null
            ? null
            : (int) $this->getData(self::SHARED_CATALOG_PERMISSION_PERMISSION);
    }

    /**
     * @inheritdoc
     */
    public function setPermission($value)
    {
        $this->setData(self::SHARED_CATALOG_PERMISSION_PERMISSION, $value);
        return $this;
    }
}
