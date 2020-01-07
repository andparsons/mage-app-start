<?php
namespace Magento\NegotiableQuoteSharedCatalog\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\SharedCatalog\Api\StatusInfoInterface as SharedCatalogModuleConfig;
use Magento\SharedCatalog\Model\SharedCatalogProductsLoader;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\NegotiableQuote\Model\Config as NegotiableQuoteModuleConfig;

/**
 * Additional actions after saving data to system config.
 */
class DeleteNegotiableQuoteItems implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $sharedCatalogModuleConfig;

    /**
     * @var \Magento\NegotiableQuote\Model\Config
     */
    private $negotiableQuoteModuleConfig;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete
     */
    private $itemDeleter;

    /**
     * @var SharedCatalogProductsLoader
     */
    private $sharedCatalogProductsLoader;

    /**
     * @param StoreManagerInterface $storeManager
     * @param SharedCatalogModuleConfig $sharedCatalogModuleConfig
     * @param NegotiableQuoteModuleConfig $negotiableQuoteModuleConfig
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter
     * @param SharedCatalogProductsLoader $sharedCatalogProductsLoader
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SharedCatalogModuleConfig $sharedCatalogModuleConfig,
        NegotiableQuoteModuleConfig $negotiableQuoteModuleConfig,
        \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement,
        \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter,
        SharedCatalogProductsLoader $sharedCatalogProductsLoader
    ) {
        $this->storeManager = $storeManager;
        $this->sharedCatalogModuleConfig = $sharedCatalogModuleConfig;
        $this->negotiableQuoteModuleConfig = $negotiableQuoteModuleConfig;
        $this->quoteManagement = $quoteManagement;
        $this->itemDeleter = $itemDeleter;
        $this->sharedCatalogProductsLoader = $sharedCatalogProductsLoader;
    }

    /**
     * @inheritdoc
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $scopeData = $this->getEventScopeData($observer);

        $isSharedCatalogActive = $this->sharedCatalogModuleConfig->isActive(
            $scopeData->getScopeType(),
            $scopeData->getScopeCode()
        );
        $isNegotiableQuoteActive = $this->negotiableQuoteModuleConfig->isActive(
            $scopeData->getScopeType(),
            $scopeData->getScopeCode()
        );

        if ($isSharedCatalogActive && $isNegotiableQuoteActive) {
            $this->deleteItemsFromQuote();
        }
    }

    /**
     * Delete unavailable quote items from negotiable quotes.
     *
     * @return void
     */
    private function deleteItemsFromQuote()
    {
        $stores = $this->sharedCatalogModuleConfig->getActiveSharedCatalogStoreIds();

        $customerGroupIds = $this->sharedCatalogProductsLoader->getUsedCustomerGroupIds();
        foreach ($customerGroupIds as $customerGroupId) {
            $productIds = $this->sharedCatalogProductsLoader->getAssignedProductsIds($customerGroupId);
            $items = $this->quoteManagement->retrieveQuoteItems($customerGroupId, $productIds, $stores, false);
            $this->itemDeleter->deleteItems($items);
        }
    }

    /**
     * Prepare scope data.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return DataObject
     */
    private function getEventScopeData(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $scopeData = new DataObject();
        $scopeType = $event->getWebsite()
            ? ScopeInterface::SCOPE_WEBSITES
            : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeData->setScopeType($scopeType);

        $scopeData->setScopeCode('');
        $scopeData->setScopeId(0);
        if ($scopeType === ScopeInterface::SCOPE_WEBSITES) {
            $website = $this->storeManager->getWebsite($event->getWebsite());
            $scopeData->setScopeCode($website->getCode());
            $scopeData->setScopeId($website->getId());
        }

        return $scopeData;
    }
}
