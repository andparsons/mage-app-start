<?php

namespace Magento\CompanyCredit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * CreditLimit mysql resource.
 */
class CreditLimit extends AbstractDb
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Class constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        PriceCurrencyInterface $priceCurrency,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('company_credit', 'entity_id');
    }

    /**
     * Change balance for credit.
     *
     * @param int $creditId
     * @param float $value
     * @param string $currency
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changeBalance($creditId, $value, $currency)
    {
        $connection = $this->getConnection();
        $condition = $this->getConnection()->quoteInto($this->getIdFieldName() . '=?', $creditId);
        $select = $this->getConnection()
            ->select()->from($this->getMainTable())->where($condition);
        $data = $connection->fetchRow($select);
        if (!empty($currency)
            && !empty($data[CreditLimitInterface::CURRENCY_CODE])
            && $currency != $data[CreditLimitInterface::CURRENCY_CODE]
        ) {
            /** @var \Magento\Directory\Model\Currency $operationCurrency */
            $operationCurrency = $this->priceCurrency->getCurrency(true, $currency);
            if ($operationCurrency->getRate($data[CreditLimitInterface::CURRENCY_CODE])) {
                $value = $operationCurrency->convert($value, $data[CreditLimitInterface::CURRENCY_CODE]);
            }
        }
        $balance = $data[CreditLimitInterface::BALANCE] + $value;
        $this->getConnection()->update(
            $this->getMainTable(),
            [CreditLimitInterface::BALANCE => $balance],
            $condition
        );
    }
}
