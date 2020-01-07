<?php

namespace Magento\Company\Model\SaveHandler;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Model\SaveHandlerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;

/**
 * Super User save handler.
 */
class SuperUser implements SaveHandlerInterface
{
    /**
     * @var \Magento\Company\Model\CompanySuperUserSave
     */
    private $companySuperUser;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory
     */
    private $companyCustomerAttributes;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\CompanySuperUserSave $companySuperUser
     * @param CompanyCustomerInterfaceFactory $companyCustomerAttributes
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\CompanySuperUserSave $companySuperUser,
        CompanyCustomerInterfaceFactory $companyCustomerAttributes
    ) {
        $this->customerRepository = $customerRepository;
        $this->companySuperUser = $companySuperUser;
        $this->companyCustomerAttributes = $companyCustomerAttributes;
    }

    /**
     * Saves customer as a company admin and sets all the related data like structure.
     *
     * @param CompanyInterface $company
     * @param CompanyInterface $initialCompany
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        if ($company->getSuperUserId() != $initialCompany->getSuperUserId()) {
            $admin = $this->customerRepository->getById($company->getSuperUserId());
            if ($admin->getExtensionAttributes()->getCompanyAttributes() === null) {
                $companyAttributes = $this->companyCustomerAttributes->create();
                $admin->getExtensionAttributes()->setCompanyAttributes($companyAttributes);
            }
            $admin->getExtensionAttributes()->getCompanyAttributes()->setCompanyId($company->getId());
            $this->customerRepository->save($admin);
            $initialAdmin = $initialCompany->getSuperUserId()
                ? $this->customerRepository->getById($initialCompany->getSuperUserId()) : null;
            $companyStatus = $company->getStatus() !== null ? (int)$company->getStatus() : null;
            $this->companySuperUser->saveCustomer($admin, $initialAdmin, $companyStatus);
        }
    }
}
