<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;

/**
 * Negotiable Quote Item Model
 */
class NegotiableQuoteItem extends AbstractExtensibleModel implements NegotiableQuoteItemInterface
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuoteItem::class);
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalPrice()
    {
        return (float)$this->getData(self::ORIGINAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalPrice($price)
    {
        return $this->setData(self::ORIGINAL_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalTaxAmount()
    {
        return (float)$this->getData(self::ORIGINAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalTaxAmount($taxAmount)
    {
        return $this->setData(self::ORIGINAL_TAX_AMOUNT, $taxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalDiscountAmount()
    {
        return (float)$this->getData(self::ORIGINAL_DISCOUNT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginalDiscountAmount($discountAmount)
    {
        return $this->setData(self::ORIGINAL_DISCOUNT_AMOUNT, $discountAmount);
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
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
