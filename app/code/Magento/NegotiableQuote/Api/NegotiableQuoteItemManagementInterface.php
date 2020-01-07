<?php

namespace Magento\NegotiableQuote\Api;

use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface for managing quote items.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteItemManagementInterface
{
    /**
     * Update custom prices in negotiable quote items.
     *
     * @param int $quoteId
     * @param bool $needSave [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateQuoteItemsCustomPrices($quoteId, $needSave = true);

    /**
     * Recalculate and save prices (product price, tax, discounts) for negotiable quote.
     *
     * @param int $quoteId
     * @param bool $needRecalculatePrice [optional]
     * @param bool $needRecalculateRule [optional]
     * @param bool $needSaveQuote [optional]
     * @param bool $needSaveItems [optional]
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function recalculateOriginalPriceTax(
        $quoteId,
        $needRecalculatePrice = false,
        $needRecalculateRule = false,
        $needSaveQuote = true,
        $needSaveItems = true
    );

    /**
     * Get original price for negotiable quote item.
     *
     * @param CartItemInterface $quoteItem
     * @param bool $isTax [optional]
     * @param bool $isDiscount [optional]
     * @return float
     */
    public function getOriginalPriceByItem(CartItemInterface $quoteItem, $isTax = true, $isDiscount = true);
}
