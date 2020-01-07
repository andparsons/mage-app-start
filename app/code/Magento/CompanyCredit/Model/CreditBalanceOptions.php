<?php

namespace Magento\CompanyCredit\Model;

use \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface;

/**
 * Credit balance data transfer object.
 */
class CreditBalanceOptions extends \Magento\Framework\DataObject implements CreditBalanceOptionsInterface
{
    /**
     * @inheritdoc
     */
    public function getPurchaseOrder()
    {
        return $this->getData(self::PURCHASE_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        return $this->setData(self::PURCHASE_ORDER, $purchaseOrder);
    }

    /**
     * @inheritdoc
     */
    public function getOrderIncrement()
    {
        return $this->getData(self::ORDER_INCREMENT);
    }

    /**
     * @inheritdoc
     */
    public function setOrderIncrement($orderIncrement)
    {
        return $this->setData(self::ORDER_INCREMENT, $orderIncrement);
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyDisplay()
    {
        return $this->getData(self::CURRENCY_DISPLAY);
    }

    /**
     * @inheritdoc
     */
    public function setCurrencyDisplay($currencyDisplay)
    {
        return $this->setData(self::CURRENCY_DISPLAY, $currencyDisplay);
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyBase()
    {
        return $this->getData(self::CURRENCY_BASE);
    }

    /**
     * @inheritdoc
     */
    public function setCurrencyBase($currencyBase)
    {
        return $this->setData(self::CURRENCY_BASE, $currencyBase);
    }
}
