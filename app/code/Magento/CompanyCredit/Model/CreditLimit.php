<?php

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Api\Data\CreditDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Credit limit data transfer object.
 */
class CreditLimit extends AbstractExtensibleModel implements CreditLimitInterface, CreditDataInterface
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'credit_limit';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'credit_limit';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::CREDIT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCompanyId()
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getCreditLimit()
    {
        return $this->getData(self::CREDIT_LIMIT);
    }

    /**
     * @inheritdoc
     */
    public function getBalance()
    {
        return $this->getData(self::BALANCE);
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * @inheritdoc
     */
    public function getExceedLimit()
    {
        return (bool)$this->getData(self::EXCEED_LIMIT);
    }

    /**
     * @inheritdoc
     */
    public function getAvailableLimit()
    {
        return $this->getCreditLimit() + $this->getBalance();
    }

    /**
     * @inheritdoc
     */
    public function getCreditComment()
    {
        return $this->getData(self::CREDIT_COMMENT);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::CREDIT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setCompanyId($companyId)
    {
        return $this->setData(self::COMPANY_ID, $companyId);
    }

    /**
     * @inheritdoc
     */
    public function setCreditLimit($creditLimit)
    {
        return $this->setData(self::CREDIT_LIMIT, $creditLimit);
    }

    /**
     * @inheritdoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * @inheritdoc
     */
    public function setExceedLimit($exceedLimit)
    {
        return $this->setData(self::EXCEED_LIMIT, $exceedLimit);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\CompanyCredit\Api\Data\CreditLimitExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
