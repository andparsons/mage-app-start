<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteTotalsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Extension attribute for quote totals model.
 */
class NegotiableQuoteTotals extends AbstractExtensibleModel implements NegotiableQuoteTotalsInterface
{
    /**
     * @inheritdoc
     */
    public function getItemsCount()
    {
        return $this->getData(self::ITEMS_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteStatus()
    {
        return $this->getData(self::QUOTE_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroup()
    {
        return $this->getData(self::CUSTOMER_GROUP);
    }

    /**
     * @inheritdoc
     */
    public function getBaseToQuoteRate()
    {
        return $this->getData(self::BASE_TO_QUOTE_RATE);
    }

    /**
     * @inheritdoc
     */
    public function getCostTotal()
    {
        return $this->getData(self::COST_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCostTotal()
    {
        return $this->getData(self::BASE_COST_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function getOriginalTotal()
    {
        return $this->getData(self::ORIGINAL_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function getBaseOriginalTotal()
    {
        return $this->getData(self::BASE_ORIGINAL_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function getOriginalTax()
    {
        return $this->getData(self::ORIGINAL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getBaseOriginalTax()
    {
        return $this->getData(self::BASE_ORIGINAL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getOriginalPriceInclTax()
    {
        return $this->getData(self::ORIGINAL_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getBaseOriginalPriceInclTax()
    {
        return $this->getData(self::BASE_ORIGINAL_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getNegotiatedPriceType()
    {
        return $this->getData(self::NEGOTIATED_PRICE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getNegotiatedPriceValue()
    {
        return $this->getData(self::NEGOTIATED_PRICE_VALUE);
    }
}
