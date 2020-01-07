<?php

namespace Magento\CompanyCredit\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class History.
 */
class History extends AbstractModel implements HistoryInterface
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'credit_history';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'credit_history';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CompanyCredit\Model\ResourceModel\History::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyCreditId()
    {
        return $this->getData(self::COMPANY_CREDIT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return $this->getData(self::USER_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCredit()
    {
        return $this->getData(self::CURRENCY_CREDIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyOperation()
    {
        return $this->getData(self::CURRENCY_OPERATION);
    }

    /**
     * {@inheritdoc}
     */
    public function getRate()
    {
        return $this->getData(self::RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getRateCredit()
    {
        return $this->getData(self::RATE_CREDIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        return $this->getData(self::BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditLimit()
    {
        return $this->getData(self::CREDIT_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLimit()
    {
        return $this->getData(self::AVAILABLE_CREDIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatetime()
    {
        return $this->getData(self::DATETIME);
    }

    /**
     * {@inheritdoc}
     */
    public function getPurchaseOrder()
    {
        return $this->getData(self::PURCHASE_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::HISTORY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompanyCreditId($companyCreditId)
    {
        return $this->setData(self::COMPANY_CREDIT_ID, $companyCreditId);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserType($userType)
    {
        return $this->setData(self::USER_TYPE, $userType);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCredit($currencyCredit)
    {
        return $this->setData(self::CURRENCY_CREDIT, $currencyCredit);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyOperation($currencyOperation)
    {
        return $this->setData(self::CURRENCY_OPERATION, $currencyOperation);
    }

    /**
     * {@inheritdoc}
     */
    public function setRate($rate)
    {
        return $this->setData(self::RATE, $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function setRateCredit($rateCredit)
    {
        return $this->setData(self::RATE_CREDIT, $rateCredit);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($balance)
    {
        return $this->setData(self::BALANCE, $balance);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditLimit($creditLimit)
    {
        return $this->setData(self::CREDIT_LIMIT, $creditLimit);
    }

    /**
     * {@inheritdoc}
     */
    public function setAvailableLimit($availableLimit)
    {
        return $this->setData(self::AVAILABLE_CREDIT, $availableLimit);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function setDatetime($datetime)
    {
        return $this->setData(self::DATETIME, $datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function setPurchaseOrder($purchaseOrder)
    {
        return $this->setData(self::PURCHASE_ORDER, $purchaseOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }
}
