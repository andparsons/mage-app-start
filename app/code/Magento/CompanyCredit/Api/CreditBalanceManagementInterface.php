<?php

namespace Magento\CompanyCredit\Api;

/**
 * Interface for management decrease and increase credit balance operations.
 *
 * @api
 * @since 100.0.0
 */
interface CreditBalanceManagementInterface
{
    /**
     * Decreases the company credit with an Update, Reimburse, or Purchase transaction.
     * This transaction increases company's outstanding balance and decreases company's available credit.
     *
     * @param int $creditId
     * @param float $value
     * @param string $currency
     * @param int $operationType
     * @param string $comment [optional]
     * @param \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface|null $options [optional]
     * @return bool true on success
     */
    public function decrease(
        $creditId,
        $value,
        $currency,
        $operationType,
        $comment = '',
        \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface $options = null
    );

    /**
     * Increases the company credit with an Allocate, Update, Refund, Revert, or Reimburse transaction.
     * This transaction decreases company's outstanding balance and increases company's available credit.
     *
     * @param int $creditId
     * @param float $value
     * @param string $currency
     * @param int $operationType
     * @param string $comment [optional]
     * @param \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface|null $options [optional]
     * @return bool true on success
     */
    public function increase(
        $creditId,
        $value,
        $currency,
        $operationType,
        $comment = '',
        \Magento\CompanyCredit\Api\Data\CreditBalanceOptionsInterface $options = null
    );
}
