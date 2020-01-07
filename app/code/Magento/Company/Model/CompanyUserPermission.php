<?php

namespace Magento\Company\Model;

/**
 * Company user permission class.
 */
class CompanyUserPermission
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $customerContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\StatusServiceInterface
     */
    private $moduleConfig;

    /**
     * CompanyAdminPermission constructor.
     *
     * @param \Magento\Authorization\Model\UserContextInterface $customerContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\StatusServiceInterface $moduleConfig
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $customerContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\StatusServiceInterface $moduleConfig
    ) {
        $this->customerContext = $customerContext;
        $this->customerRepository = $customerRepository;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Check is current user company user.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isCurrentUserCompanyUser()
    {
        $customer = $this->customerRepository->getById($this->customerContext->getUserId());
        return $this->isUserCompanyUser($customer);
    }

    /**
     * Check is user a company user.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    private function isUserCompanyUser(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->moduleConfig->isActive() &&
            $customer->getExtensionAttributes() !== null &&
            $customer->getExtensionAttributes()->getCompanyAttributes() !== null &&
            $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
    }
}
