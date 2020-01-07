<?php

namespace Magento\CompanyCredit\Api\Data;

/**
 * History data transfer object interface.
 *
 * @api
 * @since 100.0.0
 */
interface HistoryDataInterface
{
    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get company credit id.
     *
     * @return int|null
     */
    public function getCompanyCreditId();

    /**
     * Get user Id.
     *
     * @return int|null
     */
    public function getUserId();

    /**
     * Get user type: integration, admin, customer.
     *
     * @return int|null
     */
    public function getUserType();

    /**
     * Get currency code of credit.
     *
     * @return string|null
     */
    public function getCurrencyCredit();

    /**
     * Get currency code of operation.
     *
     * @return string|null
     */
    public function getCurrencyOperation();

    /**
     * Get currency rate between credit and operation currencies.
     *
     * @return float
     */
    public function getRate();

    /**
     * Get rate between credit and base currencies.
     *
     * @return float|null
     */
    public function getRateCredit();

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get outstanding balance.
     *
     * @return float
     */
    public function getBalance();

    /**
     * Get credit limit.
     *
     * @return float
     */
    public function getCreditLimit();

    /**
     * Get available limit.
     *
     * @return float|null
     */
    public function getAvailableLimit();

    /**
     * Get type of operation.
     *
     * @return int|null
     */
    public function getType();

    /**
     * Get operation datetime.
     *
     * @return string|null
     */
    public function getDatetime();

    /**
     * Get Purchase Order number.
     *
     * @return string|null
     */
    public function getPurchaseOrder();

    /**
     * Get comment.
     *
     * @return string|null
     */
    public function getComment();
}
