<?php
namespace Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel;

use Magento\Store\Model\ScopeInterface;

/**
 * Plugin for category entity.
 */
class CategoryPlugin
{
    /**
     * Customer session.
     *
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    protected $config;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    protected $customerGroupManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor for ProductCollectionSetVisibility class.
     *
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $config
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\SharedCatalog\Api\StatusInfoInterface $config,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->companyContext = $companyContext;
        $this->resource = $resource;
        $this->config = $config;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * Join shared catalog product item to product collection.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Closure $method
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProductCount(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Closure $method,
        \Magento\Catalog\Model\Category $category
    ) {
        $customerGroupId = $this->companyContext->getCustomerGroupId();
        $website = $this->storeManager->getWebsite()->getId();
        if (!$this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)
            || $this->customerGroupManagement->isMasterCatalogAvailable($customerGroupId)
        ) {
            return $method($category);
        }

        $productTable = $this->resource->getTableName('catalog_category_product');

        $select = $subject->getConnection()->select()->from(
            ['main_table' => $productTable],
            [new \Zend_Db_Expr('COUNT(main_table.product_id)')]
        )->where(
            'main_table.category_id = :category_id'
        );

        $bind = ['category_id' => (int)$category->getId()];
        $select->joinLeft(
            ['product_entity' => $this->resource->getTableName('catalog_product_entity')],
            'main_table.product_id = product_entity.entity_id',
            'sku'
        );
        $select->joinInner(
            ['shared_product' => $this->resource->getTableName(
                'shared_catalog_product_item'
            )],
            'shared_product.sku = product_entity.sku',
            'customer_group_id'
        );
        $select->where('shared_product.customer_group_id = ?', $customerGroupId);

        $counts = $subject->getConnection()->fetchOne($select, $bind);

        return intval($counts);
    }

    /**
     * Filter categories that are denied by Category Permissions.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetParentCategories(\Magento\Catalog\Model\ResourceModel\Category $subject, array $result)
    {
        return array_filter(
            $result,
            function ($category) {
                return $category->getIsActive();
            }
        );
    }
}
