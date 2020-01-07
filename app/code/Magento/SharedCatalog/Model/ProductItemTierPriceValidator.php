<?php
namespace Magento\SharedCatalog\Model;

/**
 * Class for validation tier prices for products.
 */
class ProductItemTierPriceValidator
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Product types that can have tier prices.
     *
     * @var array
     */
    private $allowedProductTypes = [];

    /**
     * @var int
     */
    private $allWebsitesOptionId = 0;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $allowedProductTypes
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $allowedProductTypes
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * Validate if tier prices have duplicates.
     *
     * @param array $tierPrices [optional]
     * @return bool
     */
    public function validateDuplicates(array $tierPrices = [])
    {
        $data = [];
        foreach ($tierPrices as $tierPrice) {
            if (isset($tierPrice['delete'])) {
                continue;
            }
            $qty = (int)$tierPrice['qty'];
            if (isset($data[$qty])) {
                array_push($data[$qty], (int)$tierPrice['website_id']);
            } else {
                $data[$qty] = [(int)$tierPrice['website_id']];
            }
        }

        foreach ($data as $qty => $websiteIds) {
            if (count($websiteIds) <= 1) {
                continue;
            }
            if (in_array(0, $websiteIds)) {
                return false;
            }
            if (count(array_unique($websiteIds)) !== count($websiteIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if tier price can be applied to the specified product type.
     *
     * @param string $productType
     * @return bool
     */
    public function isTierPriceApplicable($productType)
    {
        return in_array($productType, $this->allowedProductTypes);
    }

    /**
     * Check is it possible to change tier price.
     *
     * @param array $prices
     * @param int $websiteId
     * @return bool
     */
    public function canChangePrice(array $prices, $websiteId)
    {
        return !($websiteId && isset($prices[$this->allWebsitesOptionId]) && count($prices) == 1
            || !$websiteId && $this->existsPricePerWebsite($prices) && !$this->isPriceScopeGlobal());
    }

    /**
     * Check if website scope price exists.
     *
     * @param array $prices
     * @return bool
     */
    public function existsPricePerWebsite(array $prices)
    {
        unset($prices[$this->allWebsitesOptionId]);

        return !empty($prices);
    }

    /**
     * Checks if price scope is global.
     *
     * @return bool
     */
    public function isPriceScopeGlobal()
    {
        $scope = (int)$this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $scope == \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL;
    }
}
