<?php

namespace Magento\Company\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Class Permission
 */
class Permission implements PermissionInterface
{
    /**
     * Company locked statuses array
     */
    const COMPANY_LOCKED_STATUSES = [
        CompanyInterface::STATUS_REJECTED,
        CompanyInterface::STATUS_PENDING
    ];

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\AuthorizationInterface $authorization
    ) {
        $this->companyManagement = $companyManagement;
        $this->customerRepository = $customerRepository;
        $this->authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function isCheckoutAllowed(
        CustomerInterface $customer,
        $isNegotiableQuoteActive = false
    ) {
        $company = $this->companyManagement->getByCustomerId($customer->getId());

        if (!$company) {
            return true;
        }

        return !$this->isCompanyBlocked($customer) && $this->hasPermission($isNegotiableQuoteActive);
    }

    /**
     * {@inheritdoc}
     */
    public function isLoginAllowed(CustomerInterface $customer)
    {
        return !$this->isCompanyLocked($customer) && !$this->isCustomerLocked($customer);
    }

    /**
     * Is customer company locked.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isCompanyLocked(CustomerInterface $customer)
    {
        $company = $this->companyManagement->getByCustomerId($customer->getId());
        if ($company) {
            return in_array($company->getStatus(), self::COMPANY_LOCKED_STATUSES);
        }
        return false;
    }

    /**
     * Is customer locked.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function isCustomerLocked(CustomerInterface $customer)
    {
        $isCustomerLocked = false;
        if ($customer->getExtensionAttributes() && $customer->getExtensionAttributes()->getCompanyAttributes()
            && $customer
                ->getExtensionAttributes()
                ->getCompanyAttributes()
                ->getStatus() == CompanyCustomerInterface::STATUS_INACTIVE
        ) {
            $isCustomerLocked = true;
        }
        return $isCustomerLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompanyBlocked(CustomerInterface $customer)
    {
        $company = $this->companyManagement->getByCustomerId($customer->getId());
        return $company && $company->getStatus() == CompanyInterface::STATUS_BLOCKED;
    }

    /**
     * Check if has permission fot method.
     *
     * @param bool $isNegotiableQuoteActive
     * @return bool
     */
    private function hasPermission($isNegotiableQuoteActive = false)
    {
        if ($isNegotiableQuoteActive) {
            return $this->authorization->isAllowed('Magento_NegotiableQuote::checkout');
        } else {
            return $this->authorization->isAllowed('Magento_Sales::place_order');
        }
    }
}
