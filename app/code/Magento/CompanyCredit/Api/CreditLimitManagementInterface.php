<?php

namespace Magento\CompanyCredit\Api;

/**
 * Credit Limit management interface.
 *
 * @api
 * @since 100.0.0
 */
interface CreditLimitManagementInterface
{
    /**
     * Returns data on the credit limit for a specified company.
     *
     * @param int $companyId
     * @return \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    public function getCreditByCompanyId($companyId);
}
