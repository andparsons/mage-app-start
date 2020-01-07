<?php
namespace Magento\BundleSharedCatalog\Plugin\Bundle\Model\ResourceModel\Selection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin for join shared catalog product item to product selection collection.
 */
class CollectionPlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $config;

    /**
     * @var \Magento\SharedCatalog\Model\CustomerGroupManagement
     */
    private $customerGroupManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader
     */
    private $productLoader;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\SharedCatalog\Model\Config $config
     * @param \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $productLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\SharedCatalog\Model\Config $config,
        \Magento\SharedCatalog\Model\CustomerGroupManagement $customerGroupManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->config = $config;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->customerRepository = $customerRepository;
        $this->productLoader = $productLoader;
        $this->storeManager = $storeManager;
    }

    /**
     * Remove items on call getItems method from selection collection items if they aren't in shared catalog.
     *
     * @param SelectionCollection $collection
     * @param array $items
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetItems(
        SelectionCollection $collection,
        array $items
    ) {
        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->config->isActive(ScopeInterface::SCOPE_WEBSITE, $website) && !empty($customerId)) {
            $customer = $this->customerRepository->getById($customerId);
            if (!$this->customerGroupManagement->isMasterCatalogAvailable($customer->getGroupId())) {
                $skus = $this->productLoader->getAssignedProductsSkus($customer->getGroupId());
                foreach ($items as $key => $item) {
                    if (!in_array($item->getSku(), $skus)) {
                        unset($items[$key]);
                    }
                }
            }
        }
        return $items;
    }
}
