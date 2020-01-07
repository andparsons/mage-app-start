<?php

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Extension attribute for quote item totals model.
 */
class NegotiableQuoteItemTotals extends AbstractExtensibleModel implements NegotiableQuoteItemTotalsInterface
{
    /**
     * @inheritdoc
     */
    public function getCost()
    {
        return $this->getData(self::COST);
    }

    /**
     * @inheritdoc
     */
    public function getCatalogPrice()
    {
        return $this->getData(self::CATALOG_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCatalogPrice()
    {
        return $this->getData(self::BASE_CATALOG_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getCatalogPriceInclTax()
    {
        return $this->getData(self::CATALOG_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCatalogPriceInclTax()
    {
        return $this->getData(self::BASE_CATALOG_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getCartPrice()
    {
        return $this->getData(self::CART_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCartPrice()
    {
        return $this->getData(self::BASE_CART_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function getCartTax()
    {
        return $this->getData(self::CART_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCartTax()
    {
        return $this->getData(self::BASE_CART_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getCartPriceInclTax()
    {
        return $this->getData(self::CART_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getBaseCartPriceInclTax()
    {
        return $this->getData(self::BASE_CART_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemTotalsExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
