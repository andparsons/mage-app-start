<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface CompanyQuoteConfigInterface
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteItemInterface extends ExtensibleDataInterface
{
    /**#@+*/
    const ITEM_ID = 'quote_item_id';
    const ORIGINAL_PRICE = 'original_price';
    const ORIGINAL_TAX_AMOUNT = 'original_tax_amount';
    const ORIGINAL_DISCOUNT_AMOUNT = 'original_discount_amount';
    /**#@-*/

    /**
     * Get quote item id
     *
     * @return int
     */
    public function getItemId();

    /**
     * Set quote item id
     *
     * @param int $id
     * @return $this
     */
    public function setItemId($id);

    /**
     * Get quote item original price
     *
     * @return float
     */
    public function getOriginalPrice();

    /**
     * Set quote item original price
     *
     * @param float $price
     * @return $this
     */
    public function setOriginalPrice($price);

    /**
     * Get quote item original tax amount
     *
     * @return float
     */
    public function getOriginalTaxAmount();

    /**
     * Set quote item original tax amount
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setOriginalTaxAmount($taxAmount);

    /**
     * Get quote item original discount amount
     *
     * @return float
     */
    public function getOriginalDiscountAmount();

    /**
     * Set quote item original discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setOriginalDiscountAmount($discountAmount);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemExtensionInterface $extensionAttributes
    );
}
