<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;

/**
 * Utility class for formatting various prices.
 */
class PriceFormatter
{
    /**
     * @var \Magento\Directory\Model\Currency[]
     */
    private $currencyArray;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var NegotiableQuoteItemManagementInterface
     */
    private $negotiableQuoteItemManagement;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->priceCurrency = $priceCurrency;
        $this->negotiableQuoteItemManagement = $negotiableQuoteItemManagement;
    }

    /**
     * Get convert rate for quote item.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency
     * @param string $baseCurrency
     * @return float
     */
    private function getConvertRate(CartItemInterface $item, $quoteCurrency, $baseCurrency)
    {
        $quote = $item->getQuote();
        $rate = 1;
        if (isset($quoteCurrency) && isset($baseCurrency)) {
            if ($baseCurrency == $quote->getBaseCurrencyCode() && $quoteCurrency == $quote->getQuoteCurrencyCode()) {
                $rate = $quote->getBaseToQuoteRate();
            } else {
                $currency = $this->priceCurrency->getCurrency(null, $baseCurrency);
                $rate = $currency->getRate($quoteCurrency) ? $currency->getRate($quoteCurrency) : 1;
            }
        }

        return $rate;
    }

    /**
     * Retrieve formatted price.
     *
     * @param float $value
     * @param string $quoteCurrency
     * @return float
     */
    private function formatProductPrice($value, $quoteCurrency)
    {
        return $this->priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->storeManager->getStore(),
            $quoteCurrency
        );
    }

    /**
     * Retrieve item original price.
     *
     * @param CartItemInterface $item
     * @return float
     */
    private function retrieveOriginalPrice(CartItemInterface $item)
    {
        $price = ($item->getExtensionAttributes()
            && $item->getExtensionAttributes()->getNegotiableQuoteItem()
            && $item->getExtensionAttributes()->getNegotiableQuoteItem()->getOriginalPrice())
            ? $item->getExtensionAttributes()->getNegotiableQuoteItem()->getOriginalPrice()
            : 0;
        return $price;
    }

    /**
     * Get formatted price value including currency.
     *
     * @param float $price
     * @param string $code
     * @return string
     */
    public function formatPrice($price, $code)
    {
        if (empty($code)) {
            $code = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        }

        if (!isset($this->currencyArray[$code])) {
            $this->currencyArray[$code] = $this->currencyFactory->create();
            $this->currencyArray[$code]->load($code);
        }

        return $this->currencyArray[$code]->formatPrecision($price, 2, [], true, false);
    }

    /**
     * Format original price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency
     * @param string $baseCurrency
     * @return float
     */
    public function getFormattedOriginalPrice(CartItemInterface $item, $quoteCurrency, $baseCurrency)
    {
        $rate = $this->getConvertRate($item, $quoteCurrency, $baseCurrency);
        return $this->formatProductPrice(
            ($item->getBasePrice() - $item->getBaseDiscountAmount() / $item->getQty()) * $rate,
            $quoteCurrency
        );
    }

    /**
     * Format cart price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency
     * @param string $baseCurrency
     * @return float
     */
    public function getFormattedCartPrice(CartItemInterface $item, $quoteCurrency, $baseCurrency)
    {
        $rate = $this->getConvertRate($item, $quoteCurrency, $baseCurrency);
        return $this->formatProductPrice(
            $this->negotiableQuoteItemManagement->getOriginalPriceByItem($item) * $rate,
            $quoteCurrency
        );
    }

    /**
     * Get item total.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency
     * @param string $baseCurrency
     * @return float
     */
    public function getItemTotal(CartItemInterface $item, $quoteCurrency, $baseCurrency)
    {
        $rate = $this->getConvertRate($item, $quoteCurrency, $baseCurrency);
        return $this->formatProductPrice(
            round($this->retrieveOriginalPrice($item) * $rate, 2) * $item->getQty(),
            $quoteCurrency
        );
    }

    /**
     * Format catalog price.
     *
     * @param CartItemInterface $item
     * @param string $quoteCurrency
     * @param string $baseCurrency
     * @return float
     */
    public function getFormattedCatalogPrice(CartItemInterface $item, $quoteCurrency, $baseCurrency)
    {
        $rate = $this->getConvertRate($item, $quoteCurrency, $baseCurrency);
        return $this->formatProductPrice($this->retrieveOriginalPrice($item) * $rate, $quoteCurrency);
    }
}
