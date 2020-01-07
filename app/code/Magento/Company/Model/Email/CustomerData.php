<?php

namespace Magento\Company\Model\Email;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class for getting customer data when sending emails.
 */
class CustomerData
{
    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * Admin user model factory for creating an admin user model and getting its data through it.
     *
     * @var \Magento\User\Api\Data\UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CustomerNameGenerationInterface $customerViewHelper
     * @param CompanyRepositoryInterface $companyRepository
     * @param UserInterfaceFactory $userFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CustomerNameGenerationInterface $customerViewHelper,
        CompanyRepositoryInterface $companyRepository,
        UserInterfaceFactory $userFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->customerViewHelper = $customerViewHelper;
        $this->companyRepository = $companyRepository;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Create an object with data merged from Customer, CustomerSecure and Company.
     *
     * @param CustomerInterface $customer
     * @param int $companyId [optional]
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataObjectByCustomer(CustomerInterface $customer, $companyId = null)
    {
        $mergedCustomerData = null;
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
        $mergedCustomerData = new \Magento\Framework\DataObject($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        if ($companyId !== null) {
            $company = $this->companyRepository->get((int)$companyId);
            $mergedCustomerData->setData('companyName', $company->getCompanyName());
            $mergedCustomerData->setData('rejectReason', $company->getRejectReason());
        }
        return $mergedCustomerData;
    }

    /**
     * Gets data object of company admin.
     *
     * @param int $companyId
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataObjectSuperUser($companyId)
    {
        $company = $this->companyRepository->get($companyId);
        $companyAdmin = $this->customerRepository->getById($company->getSuperUserId());
        return $this->getDataObjectByCustomer($companyAdmin, $companyId);
    }

    /**
     * Gets data object of company sales representative.
     *
     * @param int $companyId
     * @param int $salesRepresentativeId
     * @return \Magento\Framework\DataObject|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataObjectSalesRepresentative($companyId, $salesRepresentativeId)
    {
        $mergedCustomerData = null;
        if ($companyId && $salesRepresentativeId) {
            $company = $this->companyRepository->get((int)$companyId);
            /** @var \Magento\User\Model\User $user */
            $user = $this->userFactory->create()->load($salesRepresentativeId);

            $customerData = $this->dataProcessor
                ->buildOutputDataArray($user, \Magento\User\Api\Data\UserInterface::class);
            $mergedCustomerData = new \Magento\Framework\DataObject($customerData);
            $mergedCustomerData->setData('name', $user->getName());
            $mergedCustomerData->setData('companyName', $company->getCompanyName());
        }

        return $mergedCustomerData;
    }
}
