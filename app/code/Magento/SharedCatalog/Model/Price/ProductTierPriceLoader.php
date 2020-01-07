<?php
namespace Magento\SharedCatalog\Model\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\SharedCatalog\Model\Form\Storage\Wizard;

/**
 * Class for populating storage with tier prices of products assigned to shared catalog.
 */
class ProductTierPriceLoader
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var TierPriceFetcher
     */
    private $tierPriceFetcher;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param TierPriceFetcher $tierPriceFetcher
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\SharedCatalog\Model\ProductItemTierPriceValidator $productItemTierPriceValidator,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        TierPriceFetcher $tierPriceFetcher
    ) {
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->productItemTierPriceValidator = $productItemTierPriceValidator;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->tierPriceFetcher = $tierPriceFetcher;
    }

    /**
     * Check if tier price is applicable for products and populate storage with tier prices.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @param int $sharedCatalogId
     * @param Wizard $storage
     * @return void
     */
    public function populateProductTierPrices(array $products, $sharedCatalogId, Wizard $storage)
    {
        $productSkus = [];
        foreach ($products as $product) {
            if ($this->productItemTierPriceValidator->isTierPriceApplicable($product->getTypeId())) {
                $productSkus[] = $product->getSku();
            }
        }
        if (!empty($productSkus)) {
            $this->populateTierPrices($productSkus, $sharedCatalogId, $storage);
        }
    }

    /**
     * Load products tier prices and populate storage with them.
     *
     * @param array $productSkus
     * @param int $sharedCatalogId
     * @param Wizard $storage
     * @return void
     */
    public function populateTierPrices(array $productSkus, $sharedCatalogId, Wizard $storage)
    {
        $pricesBySkus = [];
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        foreach ($this->tierPriceFetcher->fetch($sharedCatalog, $productSkus) as $tierPrice) {
            $pricesBySkus[$tierPrice->getSku()][] = $this->prepareTierPrice($tierPrice);
        }
        $storage->setTierPrices($pricesBySkus);
    }

    /**
     * Prepare tier price.
     *
     * @param TierPriceInterface $tierPrice
     * @return array
     */
    private function prepareTierPrice(TierPriceInterface $tierPrice): array
    {
        $price = [];
        $price['qty'] = (int) $tierPrice->getQuantity();
        $price['website_id'] = $tierPrice->getWebsiteId();
        if ($tierPrice->getPriceType() === TierPriceInterface::PRICE_TYPE_FIXED) {
            $price['value_type'] = TierPriceInterface::PRICE_TYPE_FIXED;
            $price['price'] = $this->formatPrice($tierPrice->getPrice());
        } else {
            $price['value_type'] = TierPriceInterface::PRICE_TYPE_DISCOUNT;
            $price['percentage_value'] = $tierPrice->getPrice();
        }

        return $price;
    }

    /**
     * Format price according to the locale of the currency.
     *
     * @param float $value
     * @return string
     */
    private function formatPrice(float $value): string
    {
        $store = $this->storeManager->getStore();
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);

        return $value;
    }
}
