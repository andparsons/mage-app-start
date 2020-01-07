<?php

namespace Magento\Company\Api;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface for retrieving various entity data objects by a given parameters and assigning customers to a company.
 *
 * @api
 * @since 100.0.0
 */
interface CompanyManagementInterface
{
    /**
     * Get company by customer Id.
     *
     * @param int $customerId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    public function getByCustomerId($customerId);

    /**
     * Get sales representative (admin user that is responsible for company) entity data object by a given user id.
     *
     * @param int $userId
     * @return string
     */
    public function getSalesRepresentative($userId);

    /**
     * Get company admin customer entity data object by a given company id.
     *
     * @param int $companyId
     * @return CustomerInterface|null
     */
    public function getAdminByCompanyId($companyId);

    /**
     * Assign customer to company.
     *
     * @param int $companyId
     * @param int $customerId
     * @return void
     */
    public function assignCustomer($companyId, $customerId);
}
