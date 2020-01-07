<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Plugin\Catalog\Api\Data;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\CatalogPermissions\Model\Permission;

/**
 * Apply category permissions to shared catalogs in case if they were changed.
 */
class CategoryInterfacePlugin
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $sharedCatalgoCustomerGroupManagement;

    /**
     * @var \Magento\CatalogPermissions\Model\Permission\Index
     */
    private $permissionIndex;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogAssignment
     */
    private $sharedCatalogAssignment;

    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface
     */
    private $sharedCatalogCategoryManagement;

    /**
     * Shared catalog permissions before category save. Used later in afterSave.
     *
     * @var array
     */
    private $sharedCatalogPermissionsBeforeSave = [];

    /**
     * Array of all shared catalog customer group IDs, linked to category.
     *
     * @var array
     */
    private $sharedCatalogCustomerGroupIds = [];

    /**
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $sharedCatalgoCustomerGroupManagement
     * @param \Magento\CatalogPermissions\Model\Permission\Index $permissionIndex
     * @param \Magento\SharedCatalog\Model\SharedCatalogAssignment $sharedCatalogAssignment
     * @param \Magento\SharedCatalog\Api\CategoryManagementInterface $sharedCatalogCategoryManagement
     */
    public function __construct(
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $sharedCatalgoCustomerGroupManagement,
        \Magento\CatalogPermissions\Model\Permission\Index $permissionIndex,
        \Magento\SharedCatalog\Model\SharedCatalogAssignment $sharedCatalogAssignment,
        \Magento\SharedCatalog\Api\CategoryManagementInterface $sharedCatalogCategoryManagement
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->sharedCatalgoCustomerGroupManagement = $sharedCatalgoCustomerGroupManagement;
        $this->permissionIndex = $permissionIndex;
        $this->sharedCatalogAssignment = $sharedCatalogAssignment;
        $this->sharedCatalogCategoryManagement = $sharedCatalogCategoryManagement;
    }

    /**
     * Prepare shared catalog category permissions on category before save.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $subject
     * @return void
     */
    public function beforeSave(\Magento\Catalog\Api\Data\CategoryInterface $subject)
    {
        $permissions = $subject->getData('permissions');
        if (!empty($permissions)) {
            $this->sharedCatalogCustomerGroupIds = $this->getSharedCatalogCustomerGroupIds($permissions);
            if (!empty($this->sharedCatalogCustomerGroupIds)) {
                $this->sharedCatalogPermissionsBeforeSave = $this->getSharedCatalogCategoryPermissions($subject);
            }
        }
    }

    /**
     * Update shared catalog category permissions on category after save.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $subject
     * @param \Magento\Catalog\Api\Data\CategoryInterface $result
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Api\Data\CategoryInterface $subject,
        \Magento\Catalog\Api\Data\CategoryInterface $result
    ) {
        if (empty($this->sharedCatalogCustomerGroupIds)) {
            return $result;
        }

        $sharedCatalogPermissionsAfterSave = $this->getSharedCatalogCategoryPermissions($result);
        $changed = $this->getChangedPermissionData(
            $sharedCatalogPermissionsAfterSave,
            $this->sharedCatalogPermissionsBeforeSave
        );

        if (!empty($changed)) {
            $changedCustomerGroupIds = array_keys($changed);
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('main_table.' . SharedCatalogInterface::CUSTOMER_GROUP_ID, $changedCustomerGroupIds, 'in')
                ->create();
            $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria)->getItems();
            foreach ($sharedCatalogs as $sharedCatalog) {
                if (!isset($changed[$sharedCatalog->getCustomerGroupId()])) {
                    continue;
                }
                $this->applyChangedPermission(
                    $sharedCatalog,
                    $changed[$sharedCatalog->getCustomerGroupId()],
                    $result->getId()
                );
            }
        }

        return $result;
    }

    /**
     * Get changed permissions data only from old permissions data and new permissions data.
     *
     * @param array $newPermissionData
     * @param array $oldPermissionData
     * @return array
     */
    private function getChangedPermissionData(array $newPermissionData, array $oldPermissionData)
    {
        $changed = [];
        foreach ($newPermissionData as $customerGroupId => $websitePermission) {
            if (!isset($oldPermissionData[$customerGroupId])
                || ($oldPermissionData[$customerGroupId] != $websitePermission)) {
                $changed[$customerGroupId] = $websitePermission;
            }
        }

        return $changed;
    }

    /**
     * Apply permissions to shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $permissionData
     * @param int $categoryId
     * @return void
     */
    private function applyChangedPermission(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog,
        array $permissionData,
        $categoryId
    ) {
        $storeId = $sharedCatalog->getStoreId();
        if ($this->isPermissionAllowedForStoreId($storeId, $permissionData) === true) {
            $this->sharedCatalogAssignment->assignProductsForCategories($sharedCatalog->getId(), [$categoryId]);
        } else {
            $this->sharedCatalogAssignment->unassignProductsForCategories(
                $sharedCatalog->getId(),
                [$categoryId],
                $this->sharedCatalogCategoryManagement->getCategories($sharedCatalog->getId())
            );
        }
    }

    /**
     * Check if permission is allowed for selected store.
     *
     * @param int $storeId
     * @param array $permissionData
     * @return bool
     */
    private function isPermissionAllowedForStoreId($storeId, array $permissionData)
    {
        $allow = false;
        if ($storeId == 0) {
            $allow = (bool)in_array(Permission::PERMISSION_ALLOW, $permissionData);
        } else {
            $sharedCatalogWebsiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            if (isset($permissionData[$sharedCatalogWebsiteId])
                && $permissionData[$sharedCatalogWebsiteId] == Permission::PERMISSION_ALLOW) {
                $allow = true;
            }
        }

        return $allow;
    }

    /**
     * Get all shared catalog customer group IDs.
     *
     * @param array $permissions
     * @return array
     */
    private function getSharedCatalogCustomerGroupIds(array $permissions)
    {
        $sharedCatalogCustomerGroupIds = $this->sharedCatalgoCustomerGroupManagement->getSharedCatalogGroupIds();
        $sharedCatalogCustomerGroupIds = array_diff(
            $sharedCatalogCustomerGroupIds,
            [\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID]
        );

        if (!empty($sharedCatalogCustomerGroupIds)) {
            $permissionsCustomerGroupIds = [];
            foreach ($permissions as $permission) {
                $permissionsCustomerGroupIds[] = $permission['customer_group_id'];
            }
            $permissionsCustomerGroupIds = array_unique($permissionsCustomerGroupIds);
            $sharedCatalogCustomerGroupIds = array_intersect(
                $sharedCatalogCustomerGroupIds,
                $permissionsCustomerGroupIds
            );
        }

        return $sharedCatalogCustomerGroupIds;
    }

    /**
     * Get shared catalog category permissions data.
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return array
     */
    private function getSharedCatalogCategoryPermissions(
        \Magento\Catalog\Api\Data\CategoryInterface $category
    ) {
        $categoriesPermissionData = $this->permissionIndex->getIndexForCategory($category->getId(), null, null);
        $data = [];
        foreach ($categoriesPermissionData as $categoryPermissionData) {
            if (in_array($categoryPermissionData['customer_group_id'], $this->sharedCatalogCustomerGroupIds)) {
                $data[$categoryPermissionData['customer_group_id']][$categoryPermissionData['website_id']] =
                    $categoryPermissionData['grant_catalog_category_view'];
            }
        }

        return $data;
    }
}
