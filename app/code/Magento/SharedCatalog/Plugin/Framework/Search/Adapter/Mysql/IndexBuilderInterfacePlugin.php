<?php
namespace Magento\SharedCatalog\Plugin\Framework\Search\Adapter\Mysql;

use Magento\Store\Model\ScopeInterface;

/**
 * Class IndexBuilderInterfacePlugin.
 */
class IndexBuilderInterfacePlugin
{
    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $config;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
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
     * Join shared catalog product item to select.
     *
     * @param \Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface $subject
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(
        \Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface $subject,
        \Magento\Framework\DB\Select $select
    ) {
        $customerGroupId = $this->companyContext->getCustomerGroupId();
        $website = $this->storeManager->getWebsite()->getId();
        if (!$this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website)
            || $this->customerGroupManagement->isMasterCatalogAvailable($customerGroupId)
        ) {
            return $select;
        }

        $select->joinLeft(
            ['product_entity' => $this->resource->getTableName('catalog_product_entity')],
            'search_index.entity_id = product_entity.entity_id',
            'sku'
        );
        $select->joinInner(
            [
                'shared_product' => $this->resource->getTableName(
                    'shared_catalog_product_item'
                )
            ],
            'shared_product.sku = product_entity.sku',
            'customer_group_id'
        );
        $select->where('shared_product.customer_group_id = ?', $customerGroupId);

        return $select;
    }
}
