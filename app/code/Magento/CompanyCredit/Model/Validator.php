<?php

namespace Magento\CompanyCredit\Model;

/**
 * Validator for company credit data.
 */
class Validator
{
    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @param WebsiteCurrency $websiteCurrency
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     */
    public function __construct(
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
    ) {
        $this->websiteCurrency = $websiteCurrency;
        $this->creditLimitManagement = $creditLimitManagement;
    }

    /**
     * Validates company credit data.
     *
     * @param array $creditData
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    public function validateCreditData(array $creditData)
    {
        $this->validateRequired($creditData);
        if (!empty($creditData['entity_id'])) {
            $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($creditData['company_id']);
            if ($creditLimit->getId() != $creditData['entity_id']) {
                throw new \Magento\Framework\Exception\InputException(
                    __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        ['fieldName' => 'company_id', 'value' => $creditData['company_id']]
                    )
                );
            }
        }
        if (!$this->websiteCurrency->isCreditCurrencyEnabled($creditData['currency_code'])) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'Invalid attribute value. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'currency_code', 'fieldValue' => $creditData['currency_code']]
                )
            );
        }
        if (isset($creditData['credit_limit']) && $creditData['credit_limit'] < 0) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'Invalid attribute value. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'credit_limit', 'fieldValue' => $creditData['credit_limit']]
                )
            );
        }
    }

    /**
     * Validates the required data.
     *
     * @param array $creditData
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    private function validateRequired(array $creditData)
    {
        if (!isset($creditData['company_id'])) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    '"%fieldName" is required. Enter and try again.',
                    ['fieldName' => 'company_id']
                )
            );
        }
        if (!isset($creditData['currency_code'])) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    '"%fieldName" is required. Enter and try again.',
                    ['fieldName' => 'currency_code']
                )
            );
        }
    }

    /**
     * Check if Company Credit exists.
     *
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @param int $creditId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function checkCompanyCreditExist(
        \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit,
        $creditId
    ) {
        if (!$creditLimit->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Requested company is not found. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => 'CompanyCreditID', 'fieldValue' => $creditId]
                )
            );
        }
    }
}
