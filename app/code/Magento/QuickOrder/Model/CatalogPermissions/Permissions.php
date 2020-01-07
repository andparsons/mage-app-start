<?php
namespace Magento\QuickOrder\Model\CatalogPermissions;

use Magento\CatalogPermissions\Model\Permission;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * CatalogPermissions extension functionality for QuickOrder search results.
 */
class Permissions
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\CatalogPermissions\Model\Permission\Index
     */
    private $permissionIndex;

    /**
     * @var \Magento\CatalogPermissions\Helper\Data
     */
    private $catalogPermissionsData;

    /**
     * @var \Magento\CatalogPermissions\App\ConfigInterface
     */
    private $permissionConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\CatalogPermissions\Model\Permission\Index $permissionIndex
     * @param \Magento\CatalogPermissions\Helper\Data $catalogPermissionsData
     * @param \Magento\CatalogPermissions\App\ConfigInterface $permissionConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\CatalogPermissions\Model\Permission\Index $permissionIndex,
        \Magento\CatalogPermissions\Helper\Data $catalogPermissionsData,
        \Magento\CatalogPermissions\App\ConfigInterface $permissionConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->permissionIndex = $permissionIndex;
        $this->catalogPermissionsData = $catalogPermissionsData;
        $this->permissionConfig = $permissionConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get customer group ID.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCustomerGroupId()
    {
        try {
            $customer = $this->customerRepository->getById($this->userContext->getUserId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return \Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID;
        }

        return $customer->getGroupId();
    }

    /**
     * Check product permissions.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isProductPermissionsValid(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        if (!$this->permissionConfig->isEnabled()) {
            return true;
        }

        $categoryIds = $product->getData('category_ids');
        if (!$categoryIds) {
            return true;
        }

        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->permissionIndex->getResource()->getConnection();
        $select = $connection->select()
            ->from($this->permissionIndex->getResource()->getTable('magento_catalogpermissions_index'))
            ->where('category_id IN (?)', $categoryIds);
        $select->where('customer_group_id = ?', $this->getCustomerGroupId());
        $select->where('website_id = ?', $this->storeManager->getWebsite()->getId());

        $data = $connection->fetchAll($select);
        $isAllowedCategoryViewGlobally = $this->catalogPermissionsData->isAllowedCategoryView();
        foreach ($data as $row) {
            if ($row['grant_catalog_category_view'] == Permission::PERMISSION_ALLOW) {
                return true;
            }
        }

        if (!count($data) && $isAllowedCategoryViewGlobally) {
            return true;
        }

        return false;
    }

    /**
     * Apply catalog permissions to product collection.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return void
     */
    public function applyPermissionsToProductCollection(ProductCollection $productCollection)
    {
        if ($this->permissionConfig->isEnabled()) {
            $select = $productCollection->getSelect();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customerGroupId = $this->getCustomerGroupId();
            $restrictedCategoryIds = $this->permissionIndex->getRestrictedCategoryIds($customerGroupId, $websiteId);
            if (count($restrictedCategoryIds)) {
                $connection = $select->getConnection();
                $select
                    ->joinLeft(
                        ['category_product' => $productCollection->getTable('catalog_category_product')],
                        'category_product.product_id = ' . ProductCollection::MAIN_TABLE_ALIAS
                        . '.entity_id',
                        []
                    )
                    ->where(
                        'category_product.category_id IS NULL'
                        . ' OR '
                        . $connection->quoteInto('category_product.category_id NOT IN (?)', $restrictedCategoryIds)
                    );
            }
            $select->distinct();
        }
    }
}
