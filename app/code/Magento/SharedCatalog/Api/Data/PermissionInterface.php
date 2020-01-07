<?php

namespace Magento\SharedCatalog\Api\Data;

/**
 * Interface for Permission model.
 */
interface PermissionInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const SHARED_CATALOG_PERMISSION_ID = 'permission_id';
    const SHARED_CATALOG_PERMISSION_CATEGORY_ID = 'category_id';
    const SHARED_CATALOG_PERMISSION_WEBSITE_ID = 'website_id';
    const SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID = 'customer_group_id';
    const SHARED_CATALOG_PERMISSION_PERMISSION = 'permission';

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return \Magento\SharedCatalog\Api\Data\PermissionInterface
     */
    public function setId($id);

    /**
     * Get category id.
     *
     * @return int
     */
    public function getCategoryId();

    /**
     * Set category id.
     *
     * @param int $value
     * @return \Magento\SharedCatalog\Api\Data\PermissionInterface
     */
    public function setCategoryId($value);

    /**
     * Get website id.
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website id.
     *
     * @param int $value
     * @return \Magento\SharedCatalog\Api\Data\PermissionInterface
     */
    public function setWebsiteId($value);

    /**
     * Get customer group id.
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set customer group id.
     *
     * @param int $value
     * @return \Magento\SharedCatalog\Api\Data\PermissionInterface
     */
    public function setCustomerGroupId($value);

    /**
     * Get permission.
     *
     * Function will return permission values assigned in \Magento\CatalogPermissions\Model\Permission
     *    value: -2 - for deny permission
     *    value: -1 - for allow permission
     *
     * @return int
     */
    public function getPermission();

    /**
     * Set permission.
     *
     * Function will return permission values assigned in \Magento\CatalogPermissions\Model\Permission
     *    value: -2 - for deny permission
     *    value: -1 - for allow permission
     *
     * @param int $value
     * @return \Magento\SharedCatalog\Api\Data\PermissionInterface
     */
    public function setPermission($value);
}
