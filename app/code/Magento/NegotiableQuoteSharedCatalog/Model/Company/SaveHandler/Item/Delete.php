<?php

namespace Magento\NegotiableQuoteSharedCatalog\Model\Company\SaveHandler\Item;

use Magento\Company\Model\SaveHandlerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\SharedCatalog\Model\SharedCatalogProductsLoader;

/**
 * Company remove forbidden quote items save handler.
 */
class Delete implements SaveHandlerInterface
{
    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete
     */
    private $itemDeleter;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Company\Api\CompanyHierarchyInterface
     */
    private $companyHierarchy;

    /**
     * @var SharedCatalogProductsLoader
     */
    private $sharedCatalogProductsLoader;

    /**
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement
     * @param \Magento\SharedCatalog\Model\Config $config
     * @param \Magento\Company\Api\CompanyHierarchyInterface $companyHierarchy
     * @param SharedCatalogProductsLoader $sharedCatalogProductsLoader
     */
    public function __construct(
        \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter,
        \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement,
        \Magento\SharedCatalog\Model\Config $config,
        \Magento\Company\Api\CompanyHierarchyInterface $companyHierarchy,
        SharedCatalogProductsLoader $sharedCatalogProductsLoader
    ) {
        $this->itemDeleter = $itemDeleter;
        $this->quoteManagement = $quoteManagement;
        $this->config = $config;
        $this->companyHierarchy = $companyHierarchy;
        $this->sharedCatalogProductsLoader = $sharedCatalogProductsLoader;
    }

    /**
     * @inheritdoc
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        if ($initialCompany->getCustomerGroupId() != $company->getCustomerGroupId()) {
            $productIdsToRemove = array_diff(
                $this->sharedCatalogProductsLoader->getAssignedProductsIds($initialCompany->getCustomerGroupId()),
                $this->sharedCatalogProductsLoader->getAssignedProductsIds($company->getCustomerGroupId())
            );
            $quoteItems = $this->quoteManagement->retrieveQuoteItemsForCustomers(
                $this->getCompanyCustomerIds($company->getId()),
                $productIdsToRemove,
                $this->config->getActiveSharedCatalogStoreIds()
            );
            $this->itemDeleter->deleteItems($quoteItems);
        }
    }

    /**
     * Retrieve customer ids for company.
     *
     * @param int $companyId
     * @return array
     */
    private function getCompanyCustomerIds($companyId)
    {
        $customerIds = [];
        $hierarchy = $this->companyHierarchy->getCompanyHierarchy($companyId);
        foreach ($hierarchy as $item) {
            if ($item->getEntityType() == \Magento\Company\Api\Data\HierarchyInterface::TYPE_CUSTOMER) {
                $customerIds[] = $item->getEntityId();
            }
        }

        return $customerIds;
    }
}
