<?php

namespace Magento\CompanyCredit\Api\Data;

/**
 * Credit Data interface.
 *
 * @api
 * @since 100.0.0
 */
interface CreditDataInterface
{
    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get company id.
     *
     * @return int|null
     */
    public function getCompanyId();

    /**
     * Get Credit Limit.
     *
     * @return float|null
     */
    public function getCreditLimit();

    /**
     * Get Balance.
     *
     * @return float|null
     */
    public function getBalance();

    /**
     * Get Currency Code.
     *
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * Get Exceed Limit.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExceedLimit();

    /**
     * Get Available Limit.
     *
     * @return float|null
     */
    public function getAvailableLimit();
}
