<?php
namespace Magento\SharedCatalog\Plugin\Catalog\Model\ResourceModel\Product;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin for Product Collection.
 */
class CollectionPlugin
{
    /**
     * Flag to determine presence shared catalog filter in collection
     */
    private const SHARED_CATALOG_FILTER = 'has_shared_catalog_filter';

    /**
     * Customer session.
     *
     * @var \Magento\Company\Model\CompanyContext
     */
    protected $companyContext;

    /**
     * @var \Magento\SharedCatalog\Model\Config
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
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * Constructor for ProductCollectionSetVisibility class.
     *
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\SharedCatalog\Model\Config $config
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\SharedCatalog\Model\Config $config,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UserContextInterface $userContext
    ) {
        $this->companyContext = $companyContext;
        $this->config = $config;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->storeManager = $storeManager;
        $this->userContext = $userContext;
    }

    /**
     * Join shared catalog product item to product collection.
     *
     * @param ProductCollection $collection
     * @param bool $printQuery [optional]
     * @param bool $logQuery [optional]
     * @return array
     */
    public function beforeLoad(
        ProductCollection $collection,
        $printQuery = false,
        $logQuery = false
    ): array {
        if (!$collection->isLoaded()) {
            $this->addSharedCatalogFilter($collection);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * Join shared catalog product item to product collection.
     *
     * @param ProductCollection $collection
     * @return array
     */
    public function beforeGetSelectCountSql(ProductCollection $collection): array
    {
        $this->addSharedCatalogFilter($collection);

        return [];
    }

    /**
     * Add shared catalog filter to collection
     *
     * @param ProductCollection $collection
     * @return void
     */
    private function addSharedCatalogFilter(ProductCollection $collection): void
    {
        // avoid adding shared catalog filter on create/edit products by api
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            || $this->userContext->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION) {
            return;
        }

        $customerGroupId = $this->companyContext->getCustomerGroupId();
        $website = $this->storeManager->getWebsite()->getId();
        if (!$collection->hasFlag(self::SHARED_CATALOG_FILTER)
            && $this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)
            && !$this->customerGroupManagement->isMasterCatalogAvailable($customerGroupId)
        ) {
            $collection->joinTable(
                ['shared_product' => $collection->getTable(
                    'shared_catalog_product_item'
                )],
                'sku = sku',
                ['customer_group_id'],
                '{{table}}.customer_group_id = \'' . $customerGroupId . '\''
            );

            $collection->setFlag(self::SHARED_CATALOG_FILTER, true);
        }
    }
}
