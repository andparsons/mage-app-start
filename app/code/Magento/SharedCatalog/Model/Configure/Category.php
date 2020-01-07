<?php

namespace Magento\SharedCatalog\Model\Configure;

/**
 * Saving configured categories.
 */
class Category
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $productSharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Api\ProductManagementInterface $productSharedCatalogManagement
     * @param \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
     */
    public function __construct(
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Api\ProductManagementInterface $productSharedCatalogManagement,
        \Magento\SharedCatalog\Model\CatalogPermissionManagement $catalogPermissionManagement
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->productSharedCatalogManagement = $productSharedCatalogManagement;
        $this->catalogPermissionManagement = $catalogPermissionManagement;
    }

    /**
     * Save configured categories.
     *
     * @param \Magento\SharedCatalog\Model\Form\Storage\Wizard $currentStorage
     * @param int $sharedCatalogId
     * @param int $storeId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function saveConfiguredCategories(
        \Magento\SharedCatalog\Model\Form\Storage\Wizard $currentStorage,
        $sharedCatalogId,
        $storeId
    ) {
        /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $productSkus = $currentStorage->getAssignedProductSkus();
        $assignedCategoriesIds = $currentStorage->getAssignedCategoriesIds();
        $unassignedCategoriesIds = $currentStorage->getUnassignedCategoriesIds();

        if ($sharedCatalog->getStoreId() === null) {
            $sharedCatalog->setStoreId($storeId);
            $this->sharedCatalogRepository->save($sharedCatalog);
        }
        if (!empty($assignedCategoriesIds) || !empty($unassignedCategoriesIds)) {
            $customerGroups = [$sharedCatalog->getCustomerGroupId()];

            if ($sharedCatalog->getType() == \Magento\SharedCatalog\Api\Data\SharedCatalogInterface::TYPE_PUBLIC) {
                $customerGroups[] = \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID;
            }

            $this->catalogPermissionManagement->setDenyPermissions($unassignedCategoriesIds, $customerGroups);
            $this->catalogPermissionManagement->setAllowPermissions($assignedCategoriesIds, $customerGroups);
        }
        $this->productSharedCatalogManagement->reassignProducts(
            $sharedCatalog,
            $productSkus
        );

        return $sharedCatalog;
    }
}
