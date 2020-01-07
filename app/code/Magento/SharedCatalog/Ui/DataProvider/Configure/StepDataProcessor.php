<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Configure;

/**
 * Prepare data for shared catalog pricing grid considering ability to configure prices for multiple websites.
 */
class StepDataProcessor
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface
     */
    private $modifiers;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Website
     */
    private $websitesDataProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var int
     */
    private $selectedWebsiteId;

    /**
     * @var string
     */
    private $websiteFilter = 'websites';

    /**
     * @var int
     */
    private $allWebsitesOptionId = 0;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Ui\DataProvider\Modifier\PoolInterface $modifiers
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SharedCatalog\Ui\DataProvider\Website $websitesDataProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Ui\DataProvider\Modifier\PoolInterface $modifiers,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SharedCatalog\Ui\DataProvider\Website $websitesDataProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->request = $request;
        $this->modifiers = $modifiers;
        $this->storeManager = $storeManager;
        $this->websitesDataProvider = $websitesDataProvider;
        $this->scopeConfig = $scopeConfig;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->localeCurrency = $localeCurrency;
    }

    /**
     * Modify data for pricing grid.
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        /** @var \Magento\Ui\DataProvider\Modifier\ModifierInterface $modifier */
        foreach ($this->modifiers->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * Set store code to fetch product price per website.
     *
     * @return void
     */
    public function switchCurrentStore()
    {
        $this->storeManager->setCurrentStore($this->getSelectedStoreCode());
    }

    /**
     * Get websites list for website select.
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = [
            'items' => $this->websitesDataProvider->getWebsites(),
            'selected' => $this->allWebsitesOptionId,
            'isPriceScopeGlobal' => $this->isPriceScopeGlobal(),
            'currencySymbol' => $this->getCurrencySymbol(),
        ];

        $selectedWebsiteId = $this->getSelectedWebsiteId();
        foreach ($websites['items'] as $website) {
            if ($website['value'] == $selectedWebsiteId) {
                $websites['selected'] = $website['value'];
                break;
            }
        }

        return $websites;
    }

    /**
     * Get currency symbol for selected website scope.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCurrencySymbol()
    {
        $websiteId = $this->getSelectedWebsiteId();
        if ($websiteId) {
            $website = $this->storeManager->getWebsite($websiteId);
            $currencyCode = $website->getBaseCurrencyCode();
        } else {
            $store = $this->storeManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            $currencyCode = $store->getBaseCurrencyCode();
        }

        return $this->localeCurrency->getCurrency($currencyCode)->getSymbol();
    }

    /**
     * Get IDs of all websites.
     *
     * @param int $sharedCatalogId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function retrieveSharedCatalogWebsiteIds($sharedCatalogId)
    {
        if ($sharedCatalogId) {
            $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
            $storeId = $sharedCatalog->getStoreId();
        }
        if (empty($storeId)) {
            $websiteIds = array_keys($this->storeManager->getWebsites());
        } else {
            $websiteIds = [$this->storeManager->getStore($storeId)->getWebsiteId()];
        }

        return $websiteIds;
    }

    /**
     * Is custom price input enabled for selected website scope.
     *
     * @param array $customPrices
     * @return bool
     */
    public function isCustomPriceEnabled(array $customPrices)
    {
        $selectedWebsiteId = $this->getSelectedWebsiteId();

        return $this->productItemTierPriceValidator->canChangePrice($customPrices, $selectedWebsiteId);
    }

    /**
     * Prepare custom price for current website scope.
     *
     * @param array $customPricesData
     * @return array|null
     */
    public function prepareCustomPrice(array $customPricesData)
    {
        $selectedWebsiteId = $this->getSelectedWebsiteId();
        $allWebsitesCustomPrices = (isset($customPricesData[$this->allWebsitesOptionId])) ?
            [$this->allWebsitesOptionId => $customPricesData[$this->allWebsitesOptionId]] : [];
        $perWebsiteCustomPrices = array_diff_key($customPricesData, $allWebsitesCustomPrices);

        if (!$this->isPriceScopeGlobal() && $selectedWebsiteId == $this->allWebsitesOptionId
            && !empty($allWebsitesCustomPrices) && !empty($perWebsiteCustomPrices)) {
            unset($customPricesData[$this->allWebsitesOptionId]);
        }

        return (isset($customPricesData[$selectedWebsiteId])) ? $customPricesData[$selectedWebsiteId] : null;
    }

    /**
     * Get id of the selected website.
     *
     * @return int
     */
    private function getSelectedWebsiteId()
    {
        if ($this->selectedWebsiteId !== null) {
            return $this->selectedWebsiteId;
        }

        $filters = $this->request->getParam('filters');
        $this->selectedWebsiteId = (isset($filters[$this->websiteFilter])) ? (int)$filters[$this->websiteFilter] :
            $this->allWebsitesOptionId;

        return $this->selectedWebsiteId;
    }

    /**
     * Get code of the first store of selected website.
     *
     * @return string
     */
    private function getSelectedStoreCode()
    {
        $selectedStoreCode = $this->storeManager->getStore()->getCode();
        $selectedWebsiteId = $this->getSelectedWebsiteId();

        if ($selectedWebsiteId) {
            $store = $this->websitesDataProvider->getStoreByWebsiteId($selectedWebsiteId);

            if ($store) {
                $selectedStoreCode = $store->getCode();
            }
        }

        return $selectedStoreCode;
    }

    /**
     * Checks if price scope is global.
     *
     * @return bool
     */
    private function isPriceScopeGlobal()
    {
        $scope = (int)$this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $scope == \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL;
    }
}
