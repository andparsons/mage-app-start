<?php

declare(strict_types=1);

namespace Magento\NegotiableQuoteSharedCatalog\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\ObserverInterface;
use Magento\SharedCatalog\Api\StatusInfoInterface as SharedCatalogModuleConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Delete unavailable items from quote after customer group change.
 */
class CustomerDeleteUnavailableNegotiableQuoteItems implements ObserverInterface
{
    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $sharedCatalogModuleConfig;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete
     */
    private $itemDeleter;

    /**
     * @param CompanyRepositoryInterface $companyRepository
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository
     * @param SharedCatalogModuleConfig $sharedCatalogModuleConfig
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository,
        SharedCatalogModuleConfig $sharedCatalogModuleConfig,
        \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter
    ) {
        $this->companyRepository = $companyRepository;
        $this->sharedCatalogProductItemRepository = $sharedCatalogProductItemRepository;
        $this->sharedCatalogModuleConfig = $sharedCatalogModuleConfig;
        $this->quoteManagement = $quoteManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->itemDeleter = $itemDeleter;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $observer->getEvent()->getData('customer_data_object');
        /** @var \Magento\Customer\Api\Data\CustomerInterface $prevCustomer */
        $prevCustomer = $observer->getEvent()->getData('orig_customer_data_object');

        if ($prevCustomer && $customer->getGroupId() != $prevCustomer->getGroupId()
            && $prevCustomer->getExtensionAttributes()
            && $prevCustomer->getExtensionAttributes()->getCompanyAttributes()
            && $prevCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
            && $this->sharedCatalogModuleConfig->getActiveSharedCatalogStoreIds()
        ) {
            $oldCompany = $this->companyRepository
                ->get($prevCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId());
            if ($oldCompany->getCustomerGroupId() != $customer->getGroupId()) {
                $productIdsToRemove = array_diff(
                    $this->retrieveProductIds($prevCustomer->getGroupId()),
                    $this->retrieveProductIds($customer->getGroupId())
                );
                $quoteItems = $this->quoteManagement->retrieveQuoteItemsForCustomers(
                    [$customer->getId()],
                    $productIdsToRemove,
                    $this->sharedCatalogModuleConfig->getActiveSharedCatalogStoreIds()
                );
                $this->itemDeleter->deleteItems($quoteItems);
            }
        }
    }

    /**
     * Retrieve product ids by customer group id.
     *
     * @param int $customerGroupId
     * @return array
     */
    private function retrieveProductIds($customerGroupId)
    {
        $this->searchCriteriaBuilder->addFilter(
            ProductItemInterface::CUSTOMER_GROUP_ID,
            $customerGroupId,
            'eq'
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->sharedCatalogProductItemRepository->getList($searchCriteria);
        $productSkus = [];
        foreach ($searchResults->getItems() as $item) {
            $productSkus[] = $item->getSku();
        }
        $products = [];
        if ($productSkus) {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter(ProductInterface::SKU, ['in' => $productSkus]);
            foreach ($productCollection as $product) {
                $products[] = $product->getEntityId();
            }
        }

        return $products;
    }
}
