<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Extension attribute for quote item totals model.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteItemTotalsInterface extends ExtensibleDataInterface
{
    const COST = 'cost';
    const CATALOG_PRICE = 'catalog_price';
    const BASE_CATALOG_PRICE = 'base_catalog_price';
    const CATALOG_PRICE_INCL_TAX = 'catalog_price_incl_tax';
    const BASE_CATALOG_PRICE_INCL_TAX = 'base_catalog_price_incl_tax';
    const CART_PRICE = 'cart_price';
    const BASE_CART_PRICE = 'base_cart_price';
    const CART_TAX = 'cart_tax';
    const BASE_CART_TAX = 'base_cart_tax';
    const CART_PRICE_INCL_TAX = 'cart_price_incl_tax';
    const BASE_CART_PRICE_INCL_TAX = 'base_cart_price_incl_tax';

    /**
     * Retrieve cost for quote item.
     *
     * @return float
     */
    public function getCost();

    /**
     * Retrieve catalog price for quote item.
     *
     * @return float
     */
    public function getCatalogPrice();

    /**
     * Retrieve catalog price for quote item in base currency.
     *
     * @return float
     */
    public function getBaseCatalogPrice();

    /**
     * Retrieve catalog price with included tax for quote item.
     *
     * @return float
     */
    public function getCatalogPriceInclTax();

    /**
     * Retrieve catalog price with included tax for quote item in base currency.
     *
     * @return float
     */
    public function getBaseCatalogPriceInclTax();

    /**
     * Retrieve cart price for quote item.
     *
     * @return float
     */
    public function getCartPrice();

    /**
     * Retrieve cart price for quote item in base currency.
     *
     * @return float
     */
    public function getBaseCartPrice();

    /**
     * Retrieve tax from catalog price for quote item.
     *
     * @return float
     */
    public function getCartTax();

    /**
     * Retrieve tax from catalog price for quote item in base currency.
     *
     * @return float
     */
    public function getBaseCartTax();

    /**
     * Retrieve cart price with included tax for quote item.
     *
     * @return float
     */
    public function getCartPriceInclTax();

    /**
     * Retrieve cart price with included tax for quote item in base currency.
     *
     * @return float
     */
    public function getBaseCartPriceInclTax();

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsExtensionInterface $extensionAttributes
    );
}
