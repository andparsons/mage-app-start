<?php
namespace Magento\SharedCatalog\Plugin\Catalog\Api;

/**
 * Delete shared catalog permissions on category delete.
 */
class DeleteSharedCatalogCategoryPermissionsPlugin
{
    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\Permission
     */
    private $sharedCatalogPermissionResource;

    /**
     * @param \Magento\SharedCatalog\Model\ResourceModel\Permission $sharedCatalogPermissionResource
     */
    public function __construct(
        \Magento\SharedCatalog\Model\ResourceModel\Permission $sharedCatalogPermissionResource
    ) {
        $this->sharedCatalogPermissionResource = $sharedCatalogPermissionResource;
    }

    /**
     * Delete Shared Catalog category permissions after deleting category.
     *
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $subject
     * @param bool $result
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Api\CategoryRepositoryInterface $subject,
        $result,
        \Magento\Catalog\Api\Data\CategoryInterface $category
    ) {
        $this->sharedCatalogPermissionResource->deleteItems($category->getId());
        return $result;
    }
}
