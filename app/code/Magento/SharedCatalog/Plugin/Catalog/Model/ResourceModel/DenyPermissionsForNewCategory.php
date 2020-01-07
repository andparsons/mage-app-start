<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Model\AbstractModel;
use Magento\SharedCatalog\Model\CatalogPermissionManagement;
use Magento\SharedCatalog\Model\State as SharedCatalogState;

/**
 * Class for setting deny permissions for the new category.
 */
class DenyPermissionsForNewCategory
{
    /**
     * @var CatalogPermissionManagement
     */
    private $catalogPermissionManagement;

    /**
     * @var SharedCatalogState
     */
    private $sharedCatalogState;

    /**
     * @param CatalogPermissionManagement $catalogPermissionManagement
     * @param SharedCatalogState $sharedCatalogState
     */
    public function __construct(
        CatalogPermissionManagement $catalogPermissionManagement,
        SharedCatalogState $sharedCatalogState
    ) {
        $this->catalogPermissionManagement = $catalogPermissionManagement;
        $this->sharedCatalogState = $sharedCatalogState;
    }

    /**
     * Deny permissions for the new category if shared catalog and category permissions features are enabled
     *
     * @param CategoryResource $subject
     * @param CategoryResource $result
     * @param AbstractModel $category
     * @return CategoryResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CategoryResource $subject,
        CategoryResource $result,
        AbstractModel $category
    ): CategoryResource {
        if ($category->isObjectNew() && $this->sharedCatalogState->isEnabled()) {
            $this->catalogPermissionManagement->setDenyPermissionsForCategory((int) $category->getId());
        }

        return $result;
    }
}
