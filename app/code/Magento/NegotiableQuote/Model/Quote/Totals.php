<?php
declare(strict_types=1);

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory;

/**
 * Class for calculate totals for negotiable quote.
 */
class Totals
{
    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * Quote tax value
     *
     * @var float|null
     */
    protected $taxValue = null;

    /**
     * Quote base tax value
     *
     * @var float|null
     */
    protected $baseTaxValue = null;

    /**
     * Quote base tax value
     *
     * @var float|null
     */
    protected $shippingTaxValue = null;

    /**
     * Conversion rate from base currency to quote currency.
     *
     * @var float|null
     */
    private $baseToQuoteCurrencyRate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var NegotiableQuoteInterface
     */
    protected $negotiableQuote;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Quote items
     *
     * @var array
     */
    private $items;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory
     */
    private $negotiableQuoteItemFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @param TaxConfig $taxConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param \Magento\NegotiableQuote\Model\NegotiableQuoteItemFactory $negotiableQuoteItemFactory
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     */
    public function __construct(
        TaxConfig $taxConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\Data\CartInterface $quote,
        NegotiableQuoteItemFactory $negotiableQuoteItemFactory,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
    ) {
        $this->taxConfig = $taxConfig;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->quote = $quote;
        $this->negotiableQuoteItemFactory = $negotiableQuoteItemFactory;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Get catalog total price without tax.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int|float
     */
    public function getCatalogTotalPriceWithoutTax($useQuoteCurrency = false)
    {
        $totalPrice = 0;

        foreach ($this->getQuoteVisibleItems() as $item) {
            $totalPrice += $this
                ->retrieveNegotiableData($item, NegotiableQuoteItemInterface::ORIGINAL_PRICE, $useQuoteCurrency);
        }

        return $totalPrice;
    }

    /**
     * Get catalog total price with tax.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int|float
     */
    public function getCatalogTotalPriceWithTax($useQuoteCurrency = false)
    {
        return $this->getCatalogTotalPriceWithoutTax($useQuoteCurrency)
            + $this->getOriginalTaxValue($useQuoteCurrency);
    }

    /**
     * Get catalog total price.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int|float
     */
    public function getCatalogTotalPrice($useQuoteCurrency = false)
    {
        $price = $this->isTaxDisplayedWithGrandTotal() ?
            $this->getCatalogTotalPriceWithTax($useQuoteCurrency) :
            $this->getCatalogTotalPriceWithoutTax($useQuoteCurrency);
        return $price - $this->getCartTotalDiscount($useQuoteCurrency);
    }

    /**
     * Get cart total discount.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int|float
     */
    public function getCartTotalDiscount($useQuoteCurrency = false)
    {
        $totalDiscount = 0;

        foreach ($this->getQuoteVisibleItems() as $item) {
            $totalDiscount += $this->retrieveNegotiableData(
                $item,
                NegotiableQuoteItemInterface::ORIGINAL_DISCOUNT_AMOUNT,
                $useQuoteCurrency
            );
        }

        return $totalDiscount;
    }

    /**
     * Get cart total tax.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int|float
     */
    public function getOriginalTaxValue($useQuoteCurrency = false)
    {
        $totalTax = 0;

        foreach ($this->getQuoteVisibleItems() as $item) {
            $totalTax += $this
                ->retrieveNegotiableData($item, NegotiableQuoteItemInterface::ORIGINAL_TAX_AMOUNT, $useQuoteCurrency);
        }

        return $totalTax;
    }

    /**
     * Get subtotal value.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    public function getSubtotal($useQuoteCurrency = false)
    {
        $subtotal = $this->getOriginalSubtotal($useQuoteCurrency);
        if ($this->isTaxDisplayedWithGrandTotal()) {
            $subtotal += $this->getSubtotalTaxValue($useQuoteCurrency);
        }

        return $subtotal;
    }

    /**
     * Get subtotal value without tax included.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    public function getSubtotalWithoutTax(bool $useQuoteCurrency = false)
    {
        return $this->getOriginalSubtotal($useQuoteCurrency);
    }

    /**
     * Get subtotal value with tax included.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    public function getSubtotalWithTax(bool $useQuoteCurrency = false)
    {
        return $this->getOriginalSubtotal($useQuoteCurrency) + $this->getSubtotalTaxValue($useQuoteCurrency);
    }

    /**
     * Get original subtotal value.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    private function getOriginalSubtotal($useQuoteCurrency = false)
    {
        $subtotal = 0;
        if ($this->getQuote()) {
            $subtotal = $useQuoteCurrency
                ? $this->getQuote()->getSubtotalWithDiscount()
                : $this->getQuote()->getBaseSubtotalWithDiscount();
        }

        return $subtotal;
    }

    /**
     * Get grand total.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    public function getGrandTotal($useQuoteCurrency = false)
    {
        $grandTotal = 0;
        if ($this->getQuote()) {
            $grandTotal = $useQuoteCurrency
                ? $this->getQuote()->getGrandTotal()
                : $this->getQuote()->getBaseGrandTotal();
        }

        return $grandTotal;
    }

    /**
     * Retrieve quote total price.
     *
     * @return float|int
     */
    public function getQuoteTotalPrice()
    {
        $totalPrice = $this->getQuote() !== null ? $this->getQuote()->getBaseSubtotalWithDiscount() : 0;

        if ($this->isTaxDisplayedWithGrandTotal()) {
            $totalPrice += $this->getSubtotalTaxValue();
        }
        return $totalPrice;
    }

    /**
     * Get quote shipping price.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    public function getQuoteShippingPrice($useQuoteCurrency = false)
    {
        if ($this->getQuote() !== null
            && $this->getQuote()->getExtensionAttributes() !== null
            && $this->getQuote()->getExtensionAttributes()->getNegotiableQuote()
            && $this->getQuote()->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice() !== null
        ) {
            $shippingPrice = $this->getQuote()->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice();
            if ($useQuoteCurrency) {
                $shippingPrice = round($shippingPrice * $this->getBaseToQuoteRate(), 2);
            }
        } else {
            $shippingPrice = $this->getAddressShippingAmount($useQuoteCurrency);
        }

        return $shippingPrice;
    }

    /**
     * Get shipping amount from address.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return int
     */
    private function getAddressShippingAmount($useQuoteCurrency = false)
    {
        $shippingAmount = 0;
        if ($this->getQuote() !== null && $this->getQuote()->getShippingAddress() !== null) {
            $address = $this->getQuote()->getShippingAddress();
            $shippingAmount = $useQuoteCurrency ? $address->getShippingAmount() : $address->getBaseShippingAmount();
        }
        return $shippingAmount;
    }

    /**
     * Get total cost.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float
     */
    public function getTotalCost($useQuoteCurrency = false)
    {
        $source = $this->getQuote();
        $totalCost = 0;
        foreach ($source->getAllVisibleItems() as $item) {
            $totalCost += $this->getItemCost($item, $useQuoteCurrency) * $item->getQty();
        }
        return $totalCost;
    }

    /**
     * Get item cost.
     *
     * @param CartItemInterface $item
     * @param bool $useQuoteCurrency [optional]
     * @return float
     */
    public function getItemCost(CartItemInterface $item, $useQuoteCurrency = false)
    {
        $totalCost = 0;
        $children = $item->getChildren();
        if (is_array($children) && count($children)) {
            foreach ($children as $child) {
                $cost = floatval($child->getProduct()->getCost());
                if ($useQuoteCurrency) {
                    $cost = round($cost * $this->getBaseToQuoteRate(), 2);
                }
                $totalCost += $cost * $child->getQty();
            }
            return $totalCost;
        }
        $cost = floatval($item->getProduct()->getCost());
        if ($useQuoteCurrency) {
            $cost = round($cost * $this->getBaseToQuoteRate(), 2);
        }
        return $cost;
    }

    /**
     * Get quote.
     *
     * @return CartInterface
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Is tax included to grand total value.
     *
     * @return bool
     */
    public function isTaxDisplayedWithGrandTotal()
    {
        return $this->taxConfig->displaySalesTaxWithGrandTotal($this->storeManager->getStore());
    }

    /**
     * Is tax included to subtotal value.
     *
     * @return bool
     */
    public function isTaxDisplayedWithSubtotal()
    {
        return $this->taxConfig->displaySalesSubtotalInclTax($this->storeManager->getStore())
        || $this->taxConfig->displaySalesSubtotalBoth($this->storeManager->getStore());
    }

    /**
     * Get tax value.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float
     */
    public function getSubtotalTaxValue($useQuoteCurrency = false)
    {
        return $this->getTaxValue($useQuoteCurrency) - $this->getShippingTaxValue($useQuoteCurrency);
    }

    /**
     * Get base tax value.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float
     */
    public function getTaxValue($useQuoteCurrency = false)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $this->getQuote()->getBillingAddress();
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
        }
        return $useQuoteCurrency ? $address->getTaxAmount() : $address->getBaseTaxAmount();
    }

    /**
     * Get shipping tax value.
     *
     * @param bool $useQuoteCurrency [optional]
     * @return float
     */
    public function getShippingTaxValue($useQuoteCurrency = false)
    {
        $shippingTaxValue = 0;
        if (!$this->getQuote()->isVirtual()) {
            $address = $this->getQuote()->getShippingAddress();
            $shippingTaxValue = $useQuoteCurrency
                ? $address->getShippingTaxAmount()
                : $address->getBaseShippingTaxAmount();
        }
        return $shippingTaxValue;
    }

    /**
     * Retrieve tax amount for quote.
     *
     * @return float
     */
    public function getTaxValueForAddInTotal()
    {
        $tax = 0;
        if (!$this->isTaxDisplayedWithGrandTotal()) {
            $tax += $this->getSubtotalTaxValue();
        }
        if (!$this->taxConfig->shippingPriceIncludesTax($this->storeManager->getStore())) {
            $tax += $this->getShippingTaxValue();
        }
        return $tax;
    }

    /**
     * Get conversion rate from base currency to quote currency.
     *
     * @return float|null
     */
    public function getBaseToQuoteRate()
    {
        if ($this->baseToQuoteCurrencyRate === null) {
            $this->baseToQuoteCurrencyRate = $this->getQuote()->getCurrency()->getBaseToQuoteRate();
        }

        return $this->baseToQuoteCurrencyRate;
    }

    /**
     * Retrieve item total price.
     *
     * @param CartItemInterface $item
     * @param string $key
     * @param bool $useQuoteCurrency [optional]
     * @return float|int
     */
    private function retrieveNegotiableData(CartItemInterface $item, $key, $useQuoteCurrency = false)
    {
        $price = 0;

        if ($item->getExtensionAttributes() !== null
            && $item->getExtensionAttributes()->getNegotiableQuoteItem() !== null) {
            $price = $item->getExtensionAttributes()->getNegotiableQuoteItem()->getData($key);
        }
        if ($useQuoteCurrency) {
            $price = round($price * $this->getBaseToQuoteRate(), 2);
        }

        return $price * $item->getQty();
    }

    /**
     * Get quote visible items.
     *
     * @param bool $useCache [optional]
     * @return array
     */
    private function getQuoteVisibleItems($useCache = true)
    {
        if (!$this->items || !$useCache) {
            $this->items = $this->getQuote()->getAllVisibleItems();
            foreach ($this->items as $key => $quoteItem) {
                if ($quoteItem->getParentItem()) {
                    unset($this->items[$key]);
                    continue;
                }
                $quoteItemExtension = $quoteItem->getExtensionAttributes();
                if (!$quoteItemExtension->getNegotiableQuoteItem()
                    || !$quoteItemExtension->getNegotiableQuoteItem()->getOriginalPrice()
                ) {
                    $negotiableItem = $this->negotiableQuoteItemFactory->create()->load($quoteItem->getItemId());
                    $negotiableItem->setItemId($quoteItem->getItemId());
                    $quoteItemExtension->setNegotiableQuoteItem($negotiableItem);
                    $quoteItem->setExtensionAttributes($quoteItemExtension);
                }
            }
        }

        return $this->items;
    }
}
