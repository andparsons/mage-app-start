<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model\Permissions;

use Magento\Catalog\Model\Category\StoreCategories;
use Magento\CatalogPermissions\Model\Permission as CatalogPermission;
use Magento\CatalogPermissions\Model\ResourceModel\Permission\CollectionFactory as CatalogPermissionsCollectionFactory;
use Magento\SharedCatalog\Model\CustomerGroupManagement;
use Magento\SharedCatalog\Model\Permission;
use Magento\SharedCatalog\Model\ResourceModel\Permission\CollectionFactory as PermissionsCollectionFactory;
use Magento\SharedCatalog\Model\State as SharedCatalogState;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Synchronizer for category permissions with shared catalog permissions.
 */
class Synchronizer
{
    /**
     * @var StoreCategories
     */
    private $storeCategories;

    /**
     * @var CatalogPermissionsCollectionFactory
     */
    private $permissionCollectionFactory;

    /**
     * @var PermissionsCollectionFactory
     */
    private $sharedCatalogPermissionCollectionFactory;

    /**
     * @var SharedCatalogState
     */
    private $sharedCatalogState;

    /**
     * @var Config
     */
    private $permissionsConfig;

    /**
     * @var CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @param StoreCategories $storeCategories
     * @param CatalogPermissionsCollectionFactory $permissionCollectionFactory
     * @param PermissionsCollectionFactory $sharedCatalogPermissionCollectionFactory
     * @param SharedCatalogState $sharedCatalogState
     * @param Config $permissionsConfig
     * @param CustomerGroupManagement $customerGroupManagement
     */
    public function __construct(
        StoreCategories $storeCategories,
        CatalogPermissionsCollectionFactory $permissionCollectionFactory,
        PermissionsCollectionFactory $sharedCatalogPermissionCollectionFactory,
        SharedCatalogState $sharedCatalogState,
        Config $permissionsConfig,
        CustomerGroupManagement $customerGroupManagement
    ) {
        $this->storeCategories = $storeCategories;
        $this->permissionCollectionFactory = $permissionCollectionFactory;
        $this->sharedCatalogPermissionCollectionFactory = $sharedCatalogPermissionCollectionFactory;
        $this->sharedCatalogState = $sharedCatalogState;
        $this->permissionsConfig = $permissionsConfig;
        $this->customerGroupManagement = $customerGroupManagement;
    }

    /**
     * Update category permissions. Do not reindex new permissions, used for scheduled job.
     *
     * @param int $categoryId
     * @param int[] $groupIds
     * @return void
     */
    public function updateCategoryPermissions(int $categoryId, array $groupIds): void
    {
        $activeWebsiteIds = array_map(
            function (WebsiteInterface $website) {
                return (int) $website->getId();
            },
            $this->sharedCatalogState->getActiveWebsites()
        );
        $permissionWebsiteIds = $this->sharedCatalogState->isGlobal() ? [null] : $activeWebsiteIds;

        $permissionCollection = $this->sharedCatalogPermissionCollectionFactory->create();
        $permissionCollection->addFieldToFilter(
            Permission::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID,
            ['in' => $groupIds]
        )->addFilter(Permission::SHARED_CATALOG_PERMISSION_CATEGORY_ID, $categoryId);
        /** @var Permission[] $sharedCategoryPermissions */
        $sharedCategoryPermissions = $permissionCollection->getItems();
        if (!$sharedCategoryPermissions) {
            foreach ($permissionWebsiteIds as $scopeId) {
                $this->removeCategoryPermission($categoryId, $scopeId, $groupIds);
            }
            return;
        }

        $updatedGroupsId = [];
        foreach ($sharedCategoryPermissions as $sharedCategoryPermission) {
            $websitesForAssign = $permissionWebsiteIds;
            $assignedWebsiteId = $sharedCategoryPermission->getWebsiteId();
            if ($assignedWebsiteId) {
                if (!in_array($assignedWebsiteId, $activeWebsiteIds)) {
                    continue;
                }

                $websitesForAssign = [$assignedWebsiteId];
            }

            foreach ($websitesForAssign as $websiteId) {
                $updatedGroupsId[] = $sharedCategoryPermission->getCustomerGroupId();
                $this->setCategoryPermission(
                    $categoryId,
                    $websiteId,
                    $sharedCategoryPermission->getCustomerGroupId(),
                    $sharedCategoryPermission->getPermission()
                );
            }
        }

        $notUpdatedGroupsId = array_diff($groupIds, array_unique($updatedGroupsId));
        foreach ($permissionWebsiteIds as $scopeId) {
            $this->removeCategoryPermission($categoryId, $scopeId, $notUpdatedGroupsId);
        }
    }

    /**
     * Remove category permission.
     *
     * @param int $categoryId
     * @param int|null $websiteId
     * @param int[] $groupIds
     * @return void
     */
    private function removeCategoryPermission(int $categoryId, ?int $websiteId, array $groupIds): void
    {
        if (!$groupIds) {
            return;
        }

        $permissionCollection = $this->permissionCollectionFactory->create();
        $permissionCollection->addFilter('category_id', $categoryId)
            ->addFieldToFilter('website_id', ['seq' => $websiteId])
            ->addFieldToFilter('customer_group_id', ['in' => $groupIds]);
        $permissionResource = $permissionCollection->getResource();
        /** @var CatalogPermission $categoryPermission */
        foreach ($permissionCollection->getItems() as $categoryPermission) {
            $permissionResource->delete($categoryPermission);
        }
    }

    /**
     * Update category permission.
     *
     * @param int $categoryId
     * @param int|null $websiteId
     * @param int $groupId
     * @param int $permission
     * @return void
     */
    private function setCategoryPermission(int $categoryId, ?int $websiteId, int $groupId, int $permission): void
    {
        $permissionCollection = $this->permissionCollectionFactory->create();
        $permissionCollection->addFilter('category_id', $categoryId)
            ->addFieldToFilter('website_id', ['seq' => $websiteId])
            ->addFieldToFilter('customer_group_id', ['seq' => $groupId]);
        /** @var CatalogPermission $categoryPermission */
        $categoryPermission = $permissionCollection->getFirstItem();

        $grantCatalogProductPrice = $this->permissionsConfig->isAllowedProductPrice($groupId, $websiteId)
            ? CatalogPermission::PERMISSION_ALLOW
            : CatalogPermission::PERMISSION_DENY;
        $grantCheckoutItems = $this->permissionsConfig->isAllowedCheckoutItems($groupId, $websiteId)
            ? CatalogPermission::PERMISSION_ALLOW
            : CatalogPermission::PERMISSION_DENY;
        $categoryPermission->setCategoryId($categoryId);
        $categoryPermission->setWebsiteId($websiteId);
        $categoryPermission->setCustomerGroupId($groupId);
        $categoryPermission->setGrantCatalogCategoryView($permission);
        $categoryPermission->setGrantCatalogProductPrice($grantCatalogProductPrice);
        $categoryPermission->setGrantCheckoutItems($grantCheckoutItems);
        $permissionCollection->getResource()
            ->save($categoryPermission);
    }

    /**
     * Remove deny category permissions.
     *
     * @param int|null $websiteId
     * @return void
     */
    public function removeCategoryPermissions(?int $websiteId): void
    {
        $categoryIds = $this->storeCategories->getCategoryIds();
        $groupIds = $this->customerGroupManagement->getSharedCatalogGroupIds();

        $permissionCollection = $this->permissionCollectionFactory->create();
        $permissionCollection->addFieldToFilter(Permission::SHARED_CATALOG_PERMISSION_CATEGORY_ID, $categoryIds);
        $permissionCollection->addFieldToFilter(Permission::SHARED_CATALOG_PERMISSION_CUSTOMER_GROUP_ID, $groupIds);
        $permissionCollection->addFieldToFilter(
            Permission::SHARED_CATALOG_PERMISSION_WEBSITE_ID,
            ['seq' => $websiteId]
        );

        $permissionResource = $permissionCollection->getResource();
        /** @var CatalogPermission[] $permissionItems */
        $permissionItems = $permissionCollection->getItems();
        foreach ($permissionItems as $permissionItem) {
            $permissionResource->delete($permissionItem);
        }
    }
}
