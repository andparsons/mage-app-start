<?php

namespace Magento\CompanyCredit\Api\Data;

/**
 * Credit Limit data transfer object interface.
 *
 * @api
 * @since 100.0.0
 */
interface CreditLimitInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Company credit entity id.
     */
    const CREDIT_ID = 'entity_id';

    /**
     * Company credit company id.
     */
    const COMPANY_ID = 'company_id';

    /**
     * Company credit limit value.
     */
    const CREDIT_LIMIT = 'credit_limit';

    /**
     * Company credit balance.
     */
    const BALANCE = 'balance';

    /**
     * Company credit currency code.
     */
    const CURRENCY_CODE = 'currency_code';

    /**
     * Company credit exceed limit.
     */
    const EXCEED_LIMIT = 'exceed_limit';

    /**
     * Company credit comment.
     */
    const CREDIT_COMMENT = 'credit_comment';

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

    /**
     * Get credit comment for company credit history.
     *
     * @return string|null
     */
    public function getCreditComment();

    /**
     * Set ID.
     *
     * @param int $id
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function setId($id);

    /**
     * Set company ID.
     *
     * @param int $companyId
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function setCompanyId($companyId);

    /**
     * Set Credit Limit.
     *
     * @param float $creditLimit
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function setCreditLimit($creditLimit);

    /**
     * Set Currency Code.
     *
     * @param string $currencyCode
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Set Exceed Limit.
     *
     * @param bool $exceedLimit
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function setExceedLimit($exceedLimit);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CompanyCredit\Api\Data\CreditLimitExtensionInterface $extensionAttributes
    );
}
