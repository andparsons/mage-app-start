<?php
namespace Magento\SharedCatalog\Plugin\CatalogPermissions\Model;

use Magento\CatalogPermissions\Model\Permission as CatalogPermission;
use Magento\SharedCatalog\Api\StatusInfoInterface;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\Store\Model\ScopeInterface;

/**
 * Update shared catalog permissions on category permission change.
 */
class UpdateSharedCatalogCategoryPermissionsPlugin
{
    /**
     * @var CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var StatusInfoInterface
     */
    private $sharedCatalogConfig;

    /**
     * @param CatalogPermissionManagement $catalogPermissionManagement
     * @param StatusInfoInterface $sharedCatalogConfig
     */
    public function __construct(
        CatalogPermissionManagement $catalogPermissionManagement,
        StatusInfoInterface $sharedCatalogConfig
    ) {
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->sharedCatalogConfig = $sharedCatalogConfig;
    }

    /**
     * Update shared catalog category permission after saving catalog category permission.
     *
     * @param CatalogPermission $subject
     * @param CatalogPermission $result
     * @return CatalogPermission
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CatalogPermission $subject,
        CatalogPermission $result
    ): CatalogPermission {
        $categoryId = $result->getCategoryId();
        $customerGroupId = $result->getCustomerGroupId();
        $websiteId = $result->getWebsiteId();
        $permission = $result->getGrantCatalogCategoryView();
        if ($this->sharedCatalogConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $websiteId)) {
            $this->catalogPermissionManagement->updateSharedCatalogPermission(
                $categoryId,
                $websiteId,
                $customerGroupId,
                $permission
            );
        }
        return $result;
    }

    /**
     * Update shared catalog category permission after deleting catalog category permission.
     *
     * @param CatalogPermission $subject
     * @param CatalogPermission $result
     * @return CatalogPermission
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        CatalogPermission $subject,
        CatalogPermission $result
    ): CatalogPermission {
        $categoryId = $result->getCategoryId();
        $customerGroupId = $result->getCustomerGroupId();
        $websiteId = $result->getWebsiteId();
        if ($this->sharedCatalogConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $websiteId)) {
            $this->catalogPermissionManagement->updateSharedCatalogPermission(
                $categoryId,
                $websiteId,
                $customerGroupId,
                CatalogPermission::PERMISSION_DENY
            );
        }
        return $result;
    }
}
