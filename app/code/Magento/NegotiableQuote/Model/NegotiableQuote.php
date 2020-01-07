<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteExtensionInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Negotiable Quote Model.
 */
class NegotiableQuote extends AbstractExtensibleModel implements NegotiableQuoteInterface
{
    /**
     * Initialize resource.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote::class);
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get negotiable quote snapshot.
     *
     * @return string
     */
    public function getSnapshot()
    {
        return $this->getData(self::SNAPSHOT);
    }

    /**
     * Set negotiable quote snapshot.
     *
     * @param string $snapshot
     * @return $this
     */
    public function setSnapshot($snapshot)
    {
        return $this->setData(self::SNAPSHOT, $snapshot);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsRegularQuote()
    {
        return $this->getData(self::IS_REGULAR_QUOTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsRegularQuote($isRegularQuote)
    {
        return $this->setData(self::IS_REGULAR_QUOTE, $isRegularQuote);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::QUOTE_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::QUOTE_STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteName()
    {
        return $this->getData(self::QUOTE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteName($quoteName)
    {
        return $this->setData(self::QUOTE_NAME, $quoteName);
    }

    /**
     * {@inheritdoc}
     */
    public function getNegotiatedPriceType()
    {
        return $this->getData(self::NEGOTIATED_PRICE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNegotiatedPriceType($type)
    {
        return $this->setData(self::NEGOTIATED_PRICE_TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getNegotiatedPriceValue()
    {
        return $this->getData(self::NEGOTIATED_PRICE_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNegotiatedPriceValue($value)
    {
        return $this->setData(self::NEGOTIATED_PRICE_VALUE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingPrice()
    {
        return $this->getData(self::SHIPPING_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingPrice($price)
    {
        return $this->setData(self::SHIPPING_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationPeriod()
    {
        return $this->getData(self::EXPIRATION_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationPeriod($expirationPeriod)
    {
        return $this->setData(self::EXPIRATION_PERIOD, $expirationPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailNotificationStatus()
    {
        return $this->getData(self::STATUS_EMAIL_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailNotificationStatus($statusEmailNotification)
    {
        return $this->setData(self::STATUS_EMAIL_NOTIFICATION, $statusEmailNotification);
    }

    /**
     * {@inheritdoc}
     */
    public function getHasUnconfirmedChanges()
    {
        return $this->getData(self::HAS_UNCONFIRMED_CHANGES);
    }

    /**
     * {@inheritdoc}
     */
    public function setHasUnconfirmedChanges($hasChanges)
    {
        return $this->setData(self::HAS_UNCONFIRMED_CHANGES, $hasChanges);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsCustomerPriceChanged()
    {
        return $this->getData(self::IS_CUSTOMER_PRICE_CHANGED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCustomerPriceChanged($isCustomerPriceChanged)
    {
        return $this->setData(self::IS_CUSTOMER_PRICE_CHANGED, $isCustomerPriceChanged);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsShippingTaxChanged()
    {
        return $this->getData(self::IS_SHIPPING_TAX_CHANGED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShippingTaxChanged($isShippingTaxChanged)
    {
        return $this->setData(self::IS_SHIPPING_TAX_CHANGED, $isShippingTaxChanged);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotifications()
    {
        return $this->getData(self::NOTIFICATIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotifications($notifications)
    {
        return $this->setData(self::NOTIFICATIONS, $notifications);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppliedRuleIds()
    {
        return $this->getData(self::APPLIED_RULE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAppliedRuleIds($ruleIds)
    {
        return $this->setData(self::APPLIED_RULE_IDS, $ruleIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsAddressDraft()
    {
        return $this->getData(self::IS_ADDRESS_DRAFT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsAddressDraft($isAddressDraft)
    {
        return $this->setData(self::IS_ADDRESS_DRAFT, $isAddressDraft);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeletedSku()
    {
        return $this->getData(self::DELETED_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setDeletedSku($deletedSku)
    {
        return $this->setData(self::DELETED_SKU, $deletedSku);
    }

    /**
     * @inheritdoc
     */
    public function getCreatorId()
    {
        return $this->getData(self::CREATOR_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCreatorId($id)
    {
        return $this->setData(self::CREATOR_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getCreatorType()
    {
        return $this->getData(self::CREATOR_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setCreatorType($type)
    {
        return $this->setData(self::CREATOR_TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getOriginalTotalPrice()
    {
        return $this->getData(self::ORIGINAL_TOTAL_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getBaseOriginalTotalPrice()
    {
        return $this->getData(self::BASE_ORIGINAL_TOTAL_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getNegotiatedTotalPrice()
    {
        return $this->getData(self::NEGOTIATED_TOTAL_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getBaseNegotiatedTotalPrice()
    {
        return $this->getData(self::BASE_NEGOTIATED_TOTAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(NegotiableQuoteExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
