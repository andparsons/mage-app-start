<?php

namespace Magento\Company\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Model\ResourceModel\Customer;
use Magento\Customer\Api\GroupManagementInterface;

/**
 * Class for creating new company for customer.
 */
class Company
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface
     */
    private $customerAttributes;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Company\Model\Company\Structure $companyStructure
     * @param CompanyCustomerInterface $customerAttributes
     * @param Customer $customerResource
     * @param GroupManagementInterface $groupManagement
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Company\Model\Company\Structure $companyStructure,
        CompanyCustomerInterface $customerAttributes,
        Customer $customerResource,
        GroupManagementInterface $groupManagement
    ) {
        $this->companyFactory = $companyFactory;
        $this->companyRepository = $companyRepository;
        $this->companyStructure = $companyStructure;
        $this->customerAttributes = $customerAttributes;
        $this->customerResource = $customerResource;
        $this->groupManagement = $groupManagement;
    }

    /**
     * Create company.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param array $companyData
     * @param string $jobTitle [optional]
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createCompany(CustomerInterface $customer, array $companyData, $jobTitle = null)
    {
        $companyDataObject = $this->companyFactory->create(['data' => $companyData]);
        if ($companyDataObject->getCustomerGroupId() === null) {
            $companyDataObject->setCustomerGroupId($this->groupManagement->getDefaultGroup()->getId());
        }
        $companyDataObject->setSuperUserId($customer->getId());
        $this->companyRepository->save($companyDataObject);

        $this->customerAttributes
            ->setCompanyId($companyDataObject->getId())
            ->setCustomerId($customer->getId());
        if ($jobTitle) {
            $this->customerAttributes->setJobTitle($jobTitle);
        }
        $this->customerResource->saveAdvancedCustomAttributes($this->customerAttributes);

        return $companyDataObject;
    }
}
