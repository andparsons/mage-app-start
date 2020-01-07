<?php

namespace Magento\CompanyCredit\Model;

/**
 * HistoryInterface interface.
 */
interface HistoryInterface
{
    /**#@+
     * Constants.
     */
    const HISTORY_ID = 'entity_id';
    const COMPANY_CREDIT_ID = 'company_credit_id';
    const USER_ID = 'user_id';
    const USER_TYPE = 'user_type';
    const CURRENCY_CREDIT = 'currency_credit';
    const CURRENCY_OPERATION = 'currency_operation';
    const RATE = 'rate';
    const RATE_CREDIT = 'rate_credit';
    const AMOUNT = 'amount';
    const BALANCE = 'balance';
    const CREDIT_LIMIT = 'credit_limit';
    const AVAILABLE_CREDIT = 'available_credit';
    const TYPE = 'type';
    const DATETIME = 'datetime';
    const PURCHASE_ORDER = 'purchase_order';
    const COMMENT = 'comment';

    const TYPE_ALLOCATED = 1;
    const TYPE_UPDATED = 2;
    const TYPE_PURCHASED = 3;
    const TYPE_REIMBURSED = 4;
    const TYPE_REFUNDED = 5;
    const TYPE_REVERTED = 6;
    /**#@-*/

    /**
     * System comment type for update exceed limit action.
     */
    const COMMENT_TYPE_UPDATE_EXCEED_LIMIT = 'exceed_limit';

    /**
     * System comment type for update currency action.
     */
    const COMMENT_TYPE_UPDATE_CURRENCY = 'update_currency';

    /**
     * System comment type for order action.
     */
    const COMMENT_TYPE_ORDER = 'order';

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
     * Get user type.
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
     * Get currency rate.
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
     * Get operation timestamp.
     *
     * @return int|null
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

    /**
     * Set ID.
     *
     * @param int $id
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setId($id);

    /**
     * Set company credit id.
     *
     * @param int $companyCreditId
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setCompanyCreditId($companyCreditId);

    /**
     * Set user Id.
     *
     * @param int $userId
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setUserId($userId);

    /**
     * Set user type.
     *
     * @param int $userType
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setUserType($userType);

    /**
     * Set currency code of credit.
     *
     * @param string $currencyCredit
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setCurrencyCredit($currencyCredit);

    /**
     * Set currency code of operation.
     *
     * @param string $currencyOperation
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setCurrencyOperation($currencyOperation);

    /**
     * Set currency rate.
     *
     * @param float $rate
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setRate($rate);

    /**
     * Set currency rate between base and credit currency.
     *
     * @param float|null $rateCredit
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setRateCredit($rateCredit);

    /**
     * Set amount.
     *
     * @param float $amount
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setAmount($amount);

    /**
     * Set outstanding balance.
     *
     * @param float $balance
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setBalance($balance);

    /**
     * Set credit limit.
     *
     * @param float $creditLimit
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setCreditLimit($creditLimit);

    /**
     * Set available limit.
     *
     * @param float $availableLimit
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setAvailableLimit($availableLimit);

    /**
     * Set type of operation.
     *
     * @param int $type
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setType($type);

    /**
     * Set operation timestamp.
     *
     * @param int $datetime
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setDatetime($datetime);

    /**
     * Set Purchase Order number.
     *
     * @param string $purchaseOrder
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setPurchaseOrder($purchaseOrder);

    /**
     * Set comment.
     *
     * @param string $comment
     * @return \Magento\CompanyCredit\Model\HistoryInterface
     */
    public function setComment($comment);
}
