<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface NegotiableQuoteInterface
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const QUOTE_ID = 'quote_id';
    const IS_REGULAR_QUOTE = 'is_regular_quote';
    const QUOTE_STATUS = 'status';
    const QUOTE_NAME = 'quote_name';
    const NEGOTIATED_PRICE_TYPE = 'negotiated_price_type';
    const NEGOTIATED_PRICE_VALUE = 'negotiated_price_value';
    const SHIPPING_PRICE = 'shipping_price';
    const EXPIRATION_PERIOD = 'expiration_period';
    const PROPOSED_PRICE = 'proposed_price';
    const SNAPSHOT = 'snapshot';
    const STATUS_EMAIL_NOTIFICATION = 'status_email_notification';
    const HAS_UNCONFIRMED_CHANGES = 'has_unconfirmed_changes';
    const IS_CUSTOMER_PRICE_CHANGED = 'is_customer_price_changed';
    const IS_SHIPPING_TAX_CHANGED = 'is_shipping_tax_changed';
    const NOTIFICATIONS = 'notifications';
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    const IS_ADDRESS_DRAFT = 'is_address_draft';
    const DELETED_SKU = 'deleted_sku';
    const CREATOR_TYPE = 'creator_type';
    const CREATOR_ID = 'creator_id';
    const ORIGINAL_TOTAL_PRICE = 'original_total_price';
    const BASE_ORIGINAL_TOTAL_PRICE = 'base_original_total_price';
    const NEGOTIATED_TOTAL_PRICE = 'negotiated_total_price';
    const BASE_NEGOTIATED_TOTAL_PRICE = 'base_negotiated_total_price';

    const NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT = 1;
    const NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT = 2;
    const NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL = 3;
    const STATUS_CREATED = 'created';
    const STATUS_SUBMITTED_BY_CUSTOMER = 'submitted_by_customer';
    const STATUS_SUBMITTED_BY_ADMIN = 'submitted_by_admin';
    const STATUS_PROCESSING_BY_CUSTOMER = 'processing_by_customer';
    const STATUS_PROCESSING_BY_ADMIN = 'processing_by_admin';
    const STATUS_ORDERED = 'ordered';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DECLINED = 'declined';
    const STATUS_CLOSED = 'closed';

    const ITEMS_CHANGED = 1;
    const DISCOUNT_CHANGED = 2;
    const DISCOUNT_LIMIT = 4;
    const DISCOUNT_REMOVED = 8;
    const TAX_CHANGED = 16;
    const ADDRESS_CHANGED = 32;
    const DISCOUNT_ADMIN_MODE = 256;
    /**#@-*/

    /**
     * Get negotiable quote ID.
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set negotiable quote ID.
     *
     * @param int $id
     * @return $this
     */
    public function setQuoteId($id);

    /**
     * Get is regular quote.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRegularQuote();

    /**
     * Set is regular quote.
     *
     * @param bool $isRegularQuote
     * @return $this
     */
    public function setIsRegularQuote($isRegularQuote);

    /**
     * Get negotiable quote status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set negotiable quote status.
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get negotiated price type.
     *
     * @return int
     */
    public function getNegotiatedPriceType();

    /**
     * Set negotiated price type.
     *
     * @param int $type
     * @return $this
     */
    public function setNegotiatedPriceType($type);

    /**
     * Get negotiated price value.
     *
     * @return float
     */
    public function getNegotiatedPriceValue();

    /**
     * Set negotiated price value.
     *
     * @param float $value
     * @return $this
     */
    public function setNegotiatedPriceValue($value);

    /**
     * Get proposed shipping price.
     *
     * @return float
     */
    public function getShippingPrice();

    /**
     * Set proposed shipping price.
     *
     * @param float $price
     * @return $this
     */
    public function setShippingPrice($price);

    /**
     * Get negotiable quote name.
     *
     * @return string
     */
    public function getQuoteName();

    /**
     * Set negotiable quote name.
     *
     * @param string $quoteName
     * @return $this
     */
    public function setQuoteName($quoteName);

    /**
     * Get expiration period.
     *
     * @return string
     */
    public function getExpirationPeriod();

    /**
     * Set expiration period.
     *
     * @param int $expirationPeriod
     * @return $this
     */
    public function setExpirationPeriod($expirationPeriod);

    /**
     * Get email notification status.
     *
     * @return int
     */
    public function getEmailNotificationStatus();

    /**
     * Set email notification status.
     *
     * @param int $statusEmailNotification
     * @return $this
     */
    public function setEmailNotificationStatus($statusEmailNotification);

    /**
     * Get has unconfirmed changes.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHasUnconfirmedChanges();

    /**
     * Set has unconfirmed changes.
     *
     * @param bool $hasChanges
     * @return $this
     */
    public function setHasUnconfirmedChanges($hasChanges);

    /**
     * Get shipping tax changes.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsShippingTaxChanged();

    /**
     * Set shipping tax changes.
     *
     * @param bool $isShippingTaxChanged
     * @return $this
     */
    public function setIsShippingTaxChanged($isShippingTaxChanged);

    /**
     * Get customer price changes.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCustomerPriceChanged();

    /**
     * Set quote notifications.
     *
     * @param int $notifications
     * @return $this
     */
    public function setNotifications($notifications);

    /**
     * Get quote notifications.
     *
     * @return int
     */
    public function getNotifications();

    /**
     * Set customer price changes.
     *
     * @param bool $isCustomerPriceChanged
     * @return $this
     */
    public function setIsCustomerPriceChanged($isCustomerPriceChanged);

    /**
     * Set quote rules.
     *
     * @param string $ruleIds
     * @return $this
     */
    public function setAppliedRuleIds($ruleIds);

    /**
     * Get quote rules.
     *
     * @return string
     */
    public function getAppliedRuleIds();

    /**
     * Get is address draft.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAddressDraft();

    /**
     * Set is address draft.
     *
     * @param bool $isAddressDraft
     * @return $this
     */
    public function setIsAddressDraft($isAddressDraft);

    /**
     * Get deleted products sku.
     *
     * @return string
     */
    public function getDeletedSku();

    /**
     * Set deleted products sku.
     *
     * @param string $deletedSku
     * @return $this
     */
    public function setDeletedSku($deletedSku);

    /**
     * Retrieve quote creator id.
     *
     * @return int
     */
    public function getCreatorId();

    /**
     * Set quote creator id.
     *
     * @param int $id
     * @return $this
     */
    public function setCreatorId($id);

    /**
     * Retrieve quote creator type.
     *
     * @return int
     */
    public function getCreatorType();

    /**
     * Set quote creator type.
     *
     * @param int $type
     * @return $this
     */
    public function setCreatorType($type);

    /**
     * Get quote original total price.
     *
     * @return float|null
     */
    public function getOriginalTotalPrice();

    /**
     * Get quote original total price in base currency.
     *
     * @return float|null
     */
    public function getBaseOriginalTotalPrice();

    /**
     * Get quote negotiated total price.
     *
     * @return float|null
     */
    public function getNegotiatedTotalPrice();

    /**
     * Get quote negotiated total price in base currency.
     *
     * @return float|null
     */
    public function getBaseNegotiatedTotalPrice();

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\NegotiableQuote\Api\Data\NegotiableQuoteExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteExtensionInterface $extensionAttributes
    );
}
