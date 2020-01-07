<?php

namespace Magento\SharedCatalog\Model;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category\StoreCategories;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\SharedCatalog\Api\CategoryManagementInterface;

/**
 * Handle category management for shared catalog.
 */
class CategoryManagement implements CategoryManagementInterface
{
    /**
     * @var SharedCatalogInvalidation
     */
    private $sharedCatalogInvalidation;

    /**
     * @var CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var SharedCatalogAssignment
     */
    private $sharedCatalogAssignment;

    /**
     * @var StoreCategories
     */
    private $storeCategories;

    /**
     * @param SharedCatalogInvalidation $sharedCatalogInvalidation
     * @param CatalogPermissionManagement $catalogPermissionManagement
     * @param SharedCatalogAssignment $sharedCatalogAssignment
     * @param StoreCategories $storeCategories
     */
    public function __construct(
        SharedCatalogInvalidation $sharedCatalogInvalidation,
        CatalogPermissionManagement $catalogPermissionManagement,
        SharedCatalogAssignment $sharedCatalogAssignment,
        StoreCategories $storeCategories
    ) {
        $this->sharedCatalogInvalidation = $sharedCatalogInvalidation;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->sharedCatalogAssignment = $sharedCatalogAssignment;
        $this->storeCategories = $storeCategories;
    }

    /**
     * @inheritdoc
     */
    public function getCategories($id)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $allCategoriesIds = $this->storeCategories->getCategoryIds((int) $sharedCatalog->getStoreId());
        $allowedCategoriesIds = $this->catalogPermissionManagement->getAllowedCategoriesIds(
            $sharedCatalog->getCustomerGroupId()
        );
        $assignedCategoriesIds = array_values(
            array_intersect($allCategoriesIds, $allowedCategoriesIds)
        );

        return $assignedCategoriesIds;
    }

    /**
     * @inheritdoc
     */
    public function assignCategories($id, array $categories)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $assignCategoriesIds = $this->retrieveCategoriesIds($categories);
        $customerGroups = $this->getSharedCatalogCustomerGroups($sharedCatalog);
        $this->catalogPermissionManagement->setAllowPermissions($assignCategoriesIds, $customerGroups);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function unassignCategories($id, array $categories)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $unassignCategoriesIds = $this->retrieveCategoriesIds($categories);
        $customerGroups = $this->getSharedCatalogCustomerGroups($sharedCatalog);
        $this->catalogPermissionManagement->setDenyPermissions($unassignCategoriesIds, $customerGroups);
        $this->sharedCatalogAssignment->unassignProductsForCategories(
            $id,
            $unassignCategoriesIds,
            $this->getCategories($id)
        );

        return true;
    }

    /**
     * Retrieve categories Ids.
     *
     * @param CategoryInterface[] $categories
     * @return int[]
     * @throws NoSuchEntityException If some of the requested categories don't exist
     */
    private function retrieveCategoriesIds(array $categories): array
    {
        $categoriesIds = [];
        foreach ($categories as $category) {
            $categoriesIds[] = $category->getId();
        }
        $allCategoriesIds = $this->storeCategories->getCategoryIds();
        $nonexistentCategoriesIds = array_diff($categoriesIds, $allCategoriesIds);
        if (!empty($nonexistentCategoriesIds)) {
            throw new NoSuchEntityException(
                __(
                    'Requested categories don\'t exist: %categoriesIds.',
                    ['categoriesIds' => implode(', ', array_unique($nonexistentCategoriesIds))]
                )
            );
        }

        return $categoriesIds;
    }

    /**
     * Get list of shared catalog customer groups.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return int[]
     */
    private function getSharedCatalogCustomerGroups(SharedCatalogInterface $sharedCatalog): array
    {
        $customerGroups = [(int) $sharedCatalog->getCustomerGroupId()];

        if ($sharedCatalog->getType() == SharedCatalogInterface::TYPE_PUBLIC) {
            $customerGroups[] = GroupInterface::NOT_LOGGED_IN_ID;
        }

        return $customerGroups;
    }
}
