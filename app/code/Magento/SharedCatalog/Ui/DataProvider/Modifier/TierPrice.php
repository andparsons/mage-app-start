<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Modifier for TierPrice.
 */
class TierPrice implements ModifierInterface
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface
     */
    private $productPriceOptions;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    private $sharedCatalog;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var int
     */
    private $allWebsitesOptionId = 0;

    /**
     * @var array
     */
    private $currencySymbols = [];

    /**
     * Default base currency code value.
     *
     * @var string
     */
    private $defaultCurrencyCodeValue = 'default';

    /**
     * Xml path for base currency value.
     *
     * @var string
     */
    private $xmlPathBaseCurrency = 'currency/options/base';

    /**
     * Store value in scope config.
     *
     * @var string
     */
    private $storeValueInScopeConfig = 'store';

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface $productPriceOptions
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface $productPriceOptions,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->productPriceOptions = $productPriceOptions;
        $this->request = $request;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->localeCurrency = $localeCurrency;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->meta['tier_price_fs']['children']['tier_price'] = $this->getTierPriceStructure();

        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $productId = $this->request->getParam('product_id');
        $data[$productId]['base_currencies'] = $this->getCurrencySymbols();

        return $data;
    }

    /**
     * Get current requested shared catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSharedCatalog()
    {
        if (!isset($this->sharedCatalog)) {
            $sharedCatalogId = (int)$this->request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
            $this->sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        }

        return $this->sharedCatalog;
    }

    /**
     * Get shared catalog store.
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStore()
    {
        return $this->storeManager->getStore($this->retrieveSharedCatalogStoreId());
    }

    /**
     * Retrieve shared catalog store ID.
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function retrieveSharedCatalogStoreId()
    {
        $sharedCatalogStoreId = $this->getSharedCatalog()->getStoreId();
        if (!isset($sharedCatalogStoreId)) {
            $sharedCatalogStoreId = $this->request->getParam('store_id');
        }

        return (int)$sharedCatalogStoreId;
    }

    /**
     * Get websites list.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWebsites()
    {
        $websites = [];
        if ($this->storeManager->isSingleStoreMode()) {
            $websites[] = [
                'label' => __('All Websites') . ' [' . $this->getBaseCurrencyCode() . ']',
                'value' => $this->allWebsitesOptionId,
            ];
            return $websites;
        }

        if ($this->retrieveSharedCatalogStoreId() != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            $store = $this->getStore();
            $website = $this->storeManager->getWebsite($store->getWebsiteId());
            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];

            return $websites;
        }

        $websites[] = [
            'label' => __('All Websites') . ' [' . $this->getBaseCurrencyCode() . ']',
            'value' => $this->allWebsitesOptionId,
        ];
        $websitesList = $this->storeManager->getWebsites();

        foreach ($websitesList as $website) {
            if ($this->isWebsiteApplicable($website->getId())) {
                $websites[] = [
                    'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                    'value' => $website->getId(),
                ];
            }
        }

        return $websites;
    }

    /**
     * Get currency symbols of base currency for each website.
     *
     * @return array
     */
    private function getCurrencySymbols()
    {
        $currencySymbols = [
            [
                'website_id' => $this->allWebsitesOptionId,
                'symbol' => $this->getCurrencySymbolByCode($this->getBaseCurrencyCode()),
            ]
        ];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $currencySymbols[] = [
                'website_id' => (int)$website->getId(),
                'symbol' => $this->getCurrencySymbolByCode($website->getBaseCurrencyCode()),
            ];
        }
        return $currencySymbols;
    }

    /**
     * Get currency symbol by currency code.
     *
     * @param string $currencyCode
     * @return string
     */
    private function getCurrencySymbolByCode($currencyCode)
    {
        if (!isset($this->currencySymbols[$currencyCode])) {
            $this->currencySymbols[$currencyCode] = $this->localeCurrency->getCurrency($currencyCode)->getSymbol();
        }

        return $this->currencySymbols[$currencyCode];
    }

    /**
     * Retrieve default value for website.
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDefaultWebsite()
    {
        if ($this->isScopeGlobal() || $this->storeManager->isSingleStoreMode()) {
            return $this->allWebsitesOptionId;
        }

        return $this->getStore()->getWebsiteId();
    }

    /**
     * Get tier price dynamic rows structure.
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTierPriceStructure()
    {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'options' => $this->getWebsites(),
                                        'value' => $this->getDefaultWebsite(),
                                        'visible' => true,
                                        'disabled' => false,
                                    ],
                                ],
                            ],
                        ],
                        'price_value' => [
                            'children' => [
                                'price' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'addbefore' => $this->getStore()->getBaseCurrency()
                                                    ->getCurrencySymbol(),
                                            ]
                                        ]
                                    ],
                                ],
                                'value_type' => [
                                    'arguments' => [
                                        'data' => [
                                            'options' => $this->productPriceOptions->toOptionArray(),
                                        ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Check that website id is applicable for product. Website is applicable for product, if product is assigned to it.
     *
     * @param int $websiteId
     * @return bool
     */
    private function isWebsiteApplicable($websiteId)
    {
        $isApplicable = false;
        $productId = $this->request->getParam('product_id');

        try {
            $product = $this->productRepository->getById($productId);
            $websiteIds = $product->getExtensionAttributes()->getWebsiteIds();
            $isApplicable = in_array($websiteId, $websiteIds);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Product does not exist, website can't be applicable.
        }

        return $isApplicable;
    }

    /**
     * Retrieve application base currency code.
     *
     * @return string
     */
    private function getBaseCurrencyCode()
    {
        return $this->scopeConfig->getValue($this->xmlPathBaseCurrency, $this->defaultCurrencyCodeValue);
    }

    /**
     * Check that price scope is global.
     *
     * @return bool
     */
    private function isScopeGlobal()
    {
        $priceScope = $this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            $this->storeValueInScopeConfig
        );

        return $priceScope == \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL;
    }
}
