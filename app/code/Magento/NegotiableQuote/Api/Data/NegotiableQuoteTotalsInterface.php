<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Extension attribute for quote totals model.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteTotalsInterface extends ExtensibleDataInterface
{
    const ITEMS_COUNT = 'items_count';
    const QUOTE_STATUS = 'quote_status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CUSTOMER_GROUP = 'customer_group';
    const BASE_TO_QUOTE_RATE = 'base_to_quote_rate';
    const COST_TOTAL = 'cost_total';
    const BASE_COST_TOTAL = 'base_cost_total';
    const ORIGINAL_TOTAL = 'original_total';
    const BASE_ORIGINAL_TOTAL = 'base_original_total';
    const ORIGINAL_TAX = 'original_tax';
    const BASE_ORIGINAL_TAX = 'base_original_tax';
    const ORIGINAL_PRICE_INCL_TAX = 'original_price_incl_tax';
    const BASE_ORIGINAL_PRICE_INCL_TAX = 'base_original_price_incl_tax';
    const NEGOTIATED_PRICE_TYPE = 'negotiated_price_type';
    const NEGOTIATED_PRICE_VALUE = 'negotiated_price_value';

    /**
     * Returns the number of different items or products in the cart.
     *
     * @return int
     */
    public function getItemsCount();

    /**
     * Retrieve negotiable quote status.
     *
     * @return string
     */
    public function getQuoteStatus();

    /**
     * Returns the cart creation date and time.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Returns the cart last update date and time.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Get customer group id.
     *
     * @return int
     */
    public function getCustomerGroup();

    /**
     * Get base currency to quote currency rate.
     *
     * @return float
     */
    public function getBaseToQuoteRate();

    /**
     * Get total cost for quote.
     *
     * @return float
     */
    public function getCostTotal();

    /**
     * Get total cost for quote in base currency.
     *
     * @return float
     */
    public function getBaseCostTotal();

    /**
     * Get original quote total.
     *
     * @return float
     */
    public function getOriginalTotal();

    /**
     * Get original quote total in base currency.
     *
     * @return float
     */
    public function getBaseOriginalTotal();

    /**
     * Get original tax amount for quote.
     *
     * @return float
     */
    public function getOriginalTax();

    /**
     * Get original tax amount for quote in base currency.
     *
     * @return float
     */
    public function getBaseOriginalTax();

    /**
     * Get original price with included tax for quote.
     *
     * @return float
     */
    public function getOriginalPriceInclTax();

    /**
     * Get original price with included tax for quote in base currency.
     *
     * @return float
     */
    public function getBaseOriginalPriceInclTax();

    /**
     * Get negotiable quote type.
     * For percentage discount return 1, for amount discount return 2, for proposed total return 3.
     *
     * @return int
     */
    public function getNegotiatedPriceType();

    /**
     * Get negotiable price value for quote.
     *
     * @return float
     */
    public function getNegotiatedPriceValue();
}
