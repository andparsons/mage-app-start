<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Model for Product Context
 */
class ProductContext implements ProductContextInterface
{
    /**
     * @var ProductHelper
     */
    private $productHelper;

    /**
     * @var PriceCurrencyInterface $priceCurrency
     */
    private $priceCurrency;

    /**
     * @var PriceInfoFactory
     */
    private $priceInfoFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductHelper $productHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceInfoFactory $priceInfoFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductHelper $productHelper,
        PriceCurrencyInterface $priceCurrency,
        PriceInfoFactory $priceInfoFactory,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productHelper = $productHelper;
        $this->priceCurrency = $priceCurrency;
        $this->priceInfoFactory = $priceInfoFactory;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function getContextData(Product $product) : array
    {
        $parentProduct = $this->productRepository->getById($product->getId());
        $manufacturer = $product->getAttributeText('manufacturer');
        $countryOfManufacture = $product->getAttributeText('country_of_manufacture');
        $context = [
            'productId' => (int) $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'topLevelSku' => $parentProduct->getSku(),
            'specialFromDate' => $product->getSpecialFromDate(),
            'specialToDate' => $product->getSpecialToDate(),
            'newFromDate' => $product->getNewsFromDate(),
            'newToDate' => $product->getNewsToDate(),
            'createdAt' => $product->getCreatedAt(),
            'updatedAt' => $product->getUpdatedAt(),
            'manufacturer' => $manufacturer ? $manufacturer : null,
            'countryOfManufacture' => $countryOfManufacture ? $countryOfManufacture : null,
            'categories' => $product->getCategoryIds(),
            'productType' => $product->getTypeId(),
            'pricing' => $this->getPricingData($product),
            'canonicalUrl' => $product->getUrlInStore(),
            'mainImageUrl' => $this->productHelper->getImageUrl($product)
        ];

        return $context;
    }

    /**
     * Get pricing data for the product context
     *
     * @param Product $product
     * @return array
     * @throws NoSuchEntityException
     */
    private function getPricingData(Product $product) : array
    {
        $priceInfo = $this->priceInfoFactory->create($product);
        $finalPrice = $priceInfo->getPrice(FinalPrice::PRICE_CODE);

        $pricing = [
            'regularPrice' => $priceInfo->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue(),
            'minimalPrice' => $finalPrice->getMinimalPrice()->getValue(),
            'maximalPrice' => $finalPrice->getMaximalPrice()->getValue(),
            'currencyCode' => $this->storeManager->getStore()->getCurrentCurrency()->getCode(),
            'tierPricing' => $this->getTierPricing($product)
        ];

        $specialPrice = $priceInfo->getPrice(SpecialPrice::PRICE_CODE)->getAmount()->getValue();
        if ($specialPrice) {
            $pricing['specialPrice'] = $specialPrice;
        }

        return $pricing;
    }

    /**
     * Get tier pricing for the product context
     *
     * @param Product $product
     * @return array
     */
    private function getTierPricing(Product $product) : array
    {
        $tierPricing = [];
        foreach ($product->getTierPrices() as $tierPrice) {
            $tierPricing[] = [
                'customerGroupId' => (int) $tierPrice->getCustomerGroupId(),
                'qty' => (float) $tierPrice->getQty(),
                'value' => $this->priceCurrency->convertAndRound($tierPrice->getValue())
            ];
        }

        return $tierPricing;
    }
}
