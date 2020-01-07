<?php
namespace Magento\SharedCatalog\Model;

use Magento\Catalog\Model\Category\StoreCategories;
use Magento\CatalogPermissions\Model\Permission as CatalogPermission;
use Magento\SharedCatalog\Model\ResourceModel\Permission as PermissionResource;
use Magento\SharedCatalog\Model\ResourceModel\Permission\CategoryPermissions\ScheduleBulk;
use Magento\SharedCatalog\Model\ResourceModel\Permission\CollectionFactory as PermissionsCollectionFactory;

/**
 * Handle category management for shared catalog.
 */
class CatalogPermissionManagement
{
    /**
     * @var ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @var PermissionsCollectionFactory
     */
    private $sharedCatalogPermissionCollectionFactory;

    /**
     * @var CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var PermissionResource
     */
    private $sharedCatalogPermissionResource;

    /**
     * @var State
     */
    private $sharedCatalogState;

    /**
     * @var StoreCategories
     */
    private $storeCategories;

    /**
     * @param ScheduleBulk $scheduleBulk
     * @param PermissionsCollectionFactory $sharedCatalogPermissionCollectionFactory
     * @param CustomerGroupManagement $customerGroupManagement
     * @param PermissionResource $sharedCatalogPermissionResource
     * @param State $sharedCatalogState
     * @param StoreCategories $storeCategories
     */
    public function __construct(
        ScheduleBulk $scheduleBulk,
        PermissionsCollectionFactory $sharedCatalogPermissionCollectionFactory,
        CustomerGroupManagement $customerGroupManagement,
        PermissionResource $sharedCatalogPermissionResource,
        State $sharedCatalogState,
        StoreCategories $storeCategories
    ) {
        $this->scheduleBulk = $scheduleBulk;
        $this->sharedCatalogPermissionCollectionFactory = $sharedCatalogPermissionCollectionFactory;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->sharedCatalogPermissionResource = $sharedCatalogPermissionResource;
        $this->sharedCatalogState = $sharedCatalogState;
        $this->storeCategories = $storeCategories;
    }

    /**
     * Get array of categories IDs with allowed permissions for provided shared catalog id.
     *
     * @param int $customerGroupId
     * @return int[]
     */
    public function getAllowedCategoriesIds(int $customerGroupId): array
    {
        return $this->sharedCatalogPermissionResource->getCategoriesWithPermission(
            $customerGroupId,
            CatalogPermission::PERMISSION_ALLOW
        );
    }

    /**
     * Set shared catalog permissions.
     *
     * @param int[] $categoryIds
     * @param int[] $groupIds
     * @param int $permission
     * @return void
     */
    private function setPermissions(array $categoryIds, array $groupIds, int $permission): void
    {
        foreach ($categoryIds as $categoryId) {
            foreach ($groupIds as $groupId) {
                $permissionItem = $this->getSharedCatalogPermission($categoryId, null, $groupId);
                $permissionItem->setPermission($permission);
                $this->sharedCatalogPermissionResource->save($permissionItem);
            }
        }

        if ($this->sharedCatalogState->isEnabled()) {
            $this->scheduleBulk->execute($categoryIds, $groupIds);
        }
    }

    /**
     * Set allow shared catalog permissions.
     *
     * @param int[] $categoryIds
     * @param int[] $groupIds
     * @return void
     */
    public function setAllowPermissions(array $categoryIds, array $groupIds): void
    {
        $this->setPermissions($categoryIds, $groupIds, CatalogPermission::PERMISSION_ALLOW);
    }

    /**
     * Set deny shared catalog permissions.
     *
     * @param int[] $categoryIds
     * @param int[] $groupIds
     * @return void
     */
    public function setDenyPermissions(array $categoryIds, array $groupIds): void
    {
        $this->setPermissions($categoryIds, $groupIds, CatalogPermission::PERMISSION_DENY);
    }

    /**
     * Set deny category permissions.
     *
     * @param int|null $websiteId
     * @return void
     */
    public function setPermissionsForAllCategories(?int $websiteId): void
    {
        $categoryIds = $this->storeCategories->getCategoryIds();
        $groupIds = $this->customerGroupManagement->getSharedCatalogGroupIds();

        foreach ($categoryIds as $categoryId) {
            foreach ($groupIds as $groupId) {
                $permission = $this->getSharedCatalogPermission($categoryId, $websiteId, $groupId);
                if ($permission->getId()) {
                    continue;
                }

                $permission->setPermission(CatalogPermission::PERMISSION_DENY);
                $this->sharedCatalogPermissionResource->save($permission);
            }
        }

        $this->scheduleBulk->execute($categoryIds, $groupIds);
    }

    /**
     * Set deny permissions by customer group for categories without specified permissions.
     *
     * @param int $customerGroupId
     * @return void
     */
    public function setDenyPermissionsForCustomerGroup(int $customerGroupId): void
    {
        $categoryIds = $this->storeCategories->getCategoryIds();
        $this->setDenyPermissions($categoryIds, [$customerGroupId]);
    }

    /**
     * Set deny category permissions.
     *
     * @param int $categoryId
     * @return void
     */
    public function setDenyPermissionsForCategory(int $categoryId): void
    {
        $groupIds = $this->customerGroupManagement->getSharedCatalogGroupIds();
        $this->setDenyPermissions([$categoryId], $groupIds);
    }

    /**
     * Remove all stored permissions for Shared Catalog.
     *
     * @param int $customerGroupId
     * @return void
     */
    public function removeAllPermissions(int $customerGroupId): void
    {
        $permissionCollection = $this->sharedCatalogPermissionCollectionFactory->create();
        $permissionCollection->addFieldToFilter(
            Permission::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID,
            $customerGroupId
        );

        $categoryIds = [];
        /** @var Permission $permission */
        foreach ($permissionCollection as $permission) {
            $categoryId = (int) $permission->getCategoryId();
            $categoryIds[$categoryId] = $categoryId;

            $this->sharedCatalogPermissionResource->delete($permission);
        }

        if ($this->sharedCatalogState->isEnabled()) {
            $this->scheduleBulk->execute($categoryIds, [$customerGroupId]);
        }
    }

    /**
     * Update Shared Catalog permission after Category Permission save.
     *
     * @param int $categoryId
     * @param int|null $websiteId
     * @param int|null $groupId
     * @param int $permission
     * @return void
     */
    public function updateSharedCatalogPermission(
        int $categoryId,
        ?int $websiteId,
        ?int $groupId,
        int $permission
    ): void {
        $permissionItem = $this->getSharedCatalogPermission($categoryId, $websiteId, $groupId);
        if ($permissionItem->getPermission() != $permission) {
            $permissionItem->setPermission($permission);
            $this->sharedCatalogPermissionResource->save($permissionItem);
        }
    }

    /**
     * Get shared catalog permission.
     *
     * @param int $categoryId
     * @param int|null $websiteId
     * @param int|null $groupId
     * @return Permission
     */
    public function getSharedCatalogPermission(int $categoryId, ?int $websiteId, ?int $groupId): Permission
    {
        $permissionCollection = $this->sharedCatalogPermissionCollectionFactory->create();
        /** @var Permission $permission */
        $permission = $permissionCollection->getNewEmptyItem();
        $data = $this->sharedCatalogPermissionResource->getPermission($categoryId, $websiteId, $groupId);
        if ($data) {
            $permission->setId($data['permission_id']);
            $permission->setCategoryId($data['category_id']);
            $permission->setWebsiteId($data['website_id']);
            $permission->setCustomerGroupId($data['customer_group_id']);
            $permission->setPermission($data['permission']);
        } else {
            $permission->setCategoryId($categoryId);
            $permission->setWebsiteId($websiteId);
            $permission->setCustomerGroupId($groupId);
        }

        return $permission;
    }
}
