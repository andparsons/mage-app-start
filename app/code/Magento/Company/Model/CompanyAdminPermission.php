<?php

namespace Magento\Company\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class for checking whether the user is company admin.
 */
class CompanyAdminPermission
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
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $customerContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $customerContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        $this->customerContext = $customerContext;
        $this->customerRepository = $customerRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Check current user is company admin.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCurrentUserCompanyAdmin()
    {
        $customer = $this->customerRepository->getById($this->customerContext->getUserId());
        return $this->isUserCompanyAdmin($customer);
    }

    /**
     * Check if given user is a company admin.
     *
     * @param int $userId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isGivenUserCompanyAdmin($userId)
    {
        $customer = $this->customerRepository->getById($userId);
        return $this->isUserCompanyAdmin($customer);
    }

    /**
     * Check if a user is a company admin.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return bool
     */
    private function isUserCompanyAdmin(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $isCompanyAdmin = false;

        if ($customer->getExtensionAttributes() !== null
            && $customer->getExtensionAttributes()->getCompanyAttributes() !== null
        ) {
            try {
                $company = $this->companyRepository->get(
                    $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
                );
                $isCompanyAdmin = $customer->getId() == $company->getSuperUserId();
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return $isCompanyAdmin;
    }
}
