<?php

/**
 * Configuration paths storage
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model;

use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config
{
    // tax notifications
    const XML_PATH_TAX_NOTIFICATION_IGNORE_DISCOUNT = 'tax/notification/ignore_discount';

    const XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_DISPLAY = 'tax/notification/ignore_price_display';

    const XML_PATH_TAX_NOTIFICATION_IGNORE_APPLY_DISCOUNT = 'tax/notification/ignore_apply_discount';

    const XML_PATH_TAX_NOTIFICATION_INFO_URL = 'tax/notification/info_url';

    // tax classes
    const CONFIG_XML_PATH_SHIPPING_TAX_CLASS = 'tax/classes/shipping_tax_class';

    // tax calculation
    const CONFIG_XML_PATH_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    const CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX = 'tax/calculation/shipping_includes_tax';

    const CONFIG_XML_PATH_BASED_ON = 'tax/calculation/based_on';

    const CONFIG_XML_PATH_APPLY_ON = 'tax/calculation/apply_tax_on';

    const CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT = 'tax/calculation/apply_after_discount';

    const CONFIG_XML_PATH_DISCOUNT_TAX = 'tax/calculation/discount_tax';

    const XML_PATH_ALGORITHM = 'tax/calculation/algorithm';

    const CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED = 'tax/calculation/cross_border_trade_enabled';

    // tax defaults
    const CONFIG_XML_PATH_DEFAULT_COUNTRY = 'tax/defaults/country';

    const CONFIG_XML_PATH_DEFAULT_REGION = 'tax/defaults/region';

    const CONFIG_XML_PATH_DEFAULT_POSTCODE = 'tax/defaults/postcode';

    /**
     * Prices display settings
     */
    const CONFIG_XML_PATH_PRICE_DISPLAY_TYPE = 'tax/display/type';

    const CONFIG_XML_PATH_DISPLAY_SHIPPING = 'tax/display/shipping';

    /**
     * Shopping cart display settings
     */
    const XML_PATH_DISPLAY_CART_PRICE = 'tax/cart_display/price';

    const XML_PATH_DISPLAY_CART_SUBTOTAL = 'tax/cart_display/subtotal';

    const XML_PATH_DISPLAY_CART_SHIPPING = 'tax/cart_display/shipping';

    /** @deprecated */
    const XML_PATH_DISPLAY_CART_DISCOUNT = 'tax/cart_display/discount';

    const XML_PATH_DISPLAY_CART_GRANDTOTAL = 'tax/cart_display/grandtotal';

    const XML_PATH_DISPLAY_CART_FULL_SUMMARY = 'tax/cart_display/full_summary';

    const XML_PATH_DISPLAY_CART_ZERO_TAX = 'tax/cart_display/zero_tax';

    /**
     * Shopping cart display settings
     */
    const XML_PATH_DISPLAY_SALES_PRICE = 'tax/sales_display/price';

    const XML_PATH_DISPLAY_SALES_SUBTOTAL = 'tax/sales_display/subtotal';

    const XML_PATH_DISPLAY_SALES_SHIPPING = 'tax/sales_display/shipping';

    /** @deprecated */
    const XML_PATH_DISPLAY_SALES_DISCOUNT = 'tax/sales_display/discount';

    const XML_PATH_DISPLAY_SALES_GRANDTOTAL = 'tax/sales_display/grandtotal';

    const XML_PATH_DISPLAY_SALES_FULL_SUMMARY = 'tax/sales_display/full_summary';

    const XML_PATH_DISPLAY_SALES_ZERO_TAX = 'tax/sales_display/zero_tax';

    const CALCULATION_STRING_SEPARATOR = '|';

    const DISPLAY_TYPE_EXCLUDING_TAX = 1;

    const DISPLAY_TYPE_INCLUDING_TAX = 2;

    const DISPLAY_TYPE_BOTH = 3;

    /**
     * Price conversion constant for positive
     */
    const PRICE_CONVERSION_PLUS = 1;

    /**
     * Price conversion constant for negative
     */
    const PRICE_CONVERSION_MINUS = 2;

    /**
     * @var bool|null
     */
    protected $_priceIncludesTax = null;

    /**
     * Flag which notify what we need use shipping prices exclude tax for calculations
     *
     * @var bool
     */
    protected $_needUseShippingExcludeTax = false;

    /**
     * @var $_shippingPriceIncludeTax bool
     */
    protected $_shippingPriceIncludeTax = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Check if prices of product in catalog include tax
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function priceIncludesTax($store = null)
    {
        if (null !== $this->_priceIncludesTax) {
            return $this->_priceIncludesTax;
        }
        return (bool)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_PRICE_INCLUDES_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Override "price includes tax" variable regardless of system configuration of any store
     *
     * @param bool|null $value
     * @return $this
     */
    public function setPriceIncludesTax($value)
    {
        if (null === $value) {
            $this->_priceIncludesTax = null;
        } else {
            $this->_priceIncludesTax = (bool)$value;
        }
        return $this;
    }

    /**
     * Check what taxes should be applied after discount
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function applyTaxAfterDiscount($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getPriceDisplayType($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get configuration setting "Apply Discount On Prices Including Tax" value
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function discountTax($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_DISCOUNT_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == 1;
    }

    /**
     * Get taxes/discounts calculation sequence.
     * This sequence depends on "Apply Customer Tax" and "Apply Discount On Prices" configuration options.
     *
     * @param   null|int|string|Store $store
     * @return  string
     */
    public function getCalculationSequence($store = null)
    {
        if ($this->applyTaxAfterDiscount($store)) {
            if ($this->discountTax($store)) {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL;
            } else {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL;
            }
        } else {
            if ($this->discountTax($store)) {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL;
            } else {
                $seq = \Magento\Tax\Model\Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL;
            }
        }
        return $seq;
    }

    /**
     * Specify flag what we need use shipping price exclude tax
     *
     * @param   bool $flag
     * @return  \Magento\Tax\Model\Config
     */
    public function setNeedUseShippingExcludeTax($flag)
    {
        $this->_needUseShippingExcludeTax = $flag;
        return $this;
    }

    /**
     * Get flag what we need use shipping price exclude tax
     *
     * @return bool $flag
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getNeedUseShippingExcludeTax()
    {
        return $this->_needUseShippingExcludeTax;
    }

    /**
     * Get defined tax calculation algorithm
     *
     * @param   null|string|bool|int|Store $store
     * @return  string
     */
    public function getAlgorithm($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ALGORITHM,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getShippingTaxClass($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get shipping methods prices display type
     *
     * @param   null|string|bool|int|Store $store
     * @return  int
     */
    public function getShippingPriceDisplayType($store = null)
    {
        return (int)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_DISPLAY_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if shipping prices include tax
     *
     * @param   null|string|bool|int|Store $store
     * @return  bool
     */
    public function shippingPriceIncludesTax($store = null)
    {
        if ($this->_shippingPriceIncludeTax === null) {
            $this->_shippingPriceIncludeTax = (bool)$this->_scopeConfig->getValue(
                self::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return $this->_shippingPriceIncludeTax;
    }

    /**
     * Declare shipping prices type
     *
     * @param bool $flag
     * @return $this
     */
    public function setShippingPriceIncludeTax($flag)
    {
        $this->_shippingPriceIncludeTax = $flag;
        return $this;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartPricesBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartSubtotalBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartShippingBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displayCartDiscountInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displayCartDiscountExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displayCartDiscountBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartTaxWithGrandTotal($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_GRANDTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartFullSummary($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_FULL_SUMMARY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displayCartZeroTax($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_CART_ZERO_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesPricesBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesSubtotalBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesShippingBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_SHIPPING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displaySalesDiscountInclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displaySalesDiscountExclTax($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_EXCLUDING_TAX;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     * @deprecated 100.1.3
     */
    public function displaySalesDiscountBoth($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ) == self::DISPLAY_TYPE_BOTH;
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesTaxWithGrandTotal($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_GRANDTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesFullSummary($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_FULL_SUMMARY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function displaySalesZeroTax($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SALES_ZERO_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return the config value for self::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function crossBorderTradeEnabled($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::CONFIG_XML_PATH_CROSS_BORDER_TRADE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if admin notification related to misconfiguration of "Apply Discount On Prices" should be ignored.
     *
     * Warning is displayed in case when "Catalog Prices" = "Excluding Tax"
     * AND "Apply Discount On Prices" = "Including Tax"
     * AND "Apply Customer Tax" = "After Discount"
     *
     * @param null|string|Store $store
     * @return bool
     */
    public function isWrongApplyDiscountSettingIgnored($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_TAX_NOTIFICATION_IGNORE_APPLY_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if do not show notification about wrong display settings
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isWrongDisplaySettingsIgnored($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_TAX_NOTIFICATION_IGNORE_PRICE_DISPLAY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if do not show notification about wrong discount settings
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isWrongDiscountSettingsIgnored($store = null)
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_TAX_NOTIFICATION_IGNORE_DISCOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return the notification info url
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getInfoUrl($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            self::XML_PATH_TAX_NOTIFICATION_INFO_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if necessary do product price conversion
     * If it necessary will be returned conversion type (minus or plus)
     *
     * @param null|int|string|Store $store
     * @return bool|int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function needPriceConversion($store = null)
    {
        $res = 0;
        $priceIncludesTax = $this->priceIncludesTax($store) || $this->getNeedUseShippingExcludeTax();
        if ($priceIncludesTax) {
            switch ($this->getPriceDisplayType($store)) {
                case self::DISPLAY_TYPE_EXCLUDING_TAX:
                case self::DISPLAY_TYPE_BOTH:
                    return self::PRICE_CONVERSION_MINUS;
                case self::DISPLAY_TYPE_INCLUDING_TAX:
                    $res = false;
                    break;
                default:
                    break;
            }
        } else {
            switch ($this->getPriceDisplayType($store)) {
                case self::DISPLAY_TYPE_INCLUDING_TAX:
                case self::DISPLAY_TYPE_BOTH:
                    return self::PRICE_CONVERSION_PLUS;
                case self::DISPLAY_TYPE_EXCLUDING_TAX:
                    $res = false;
                    break;
                default:
                    break;
            }
        }

        if ($res === false) {
            $res = $this->displayCartPricesBoth();
        }
        return $res;
    }
}
