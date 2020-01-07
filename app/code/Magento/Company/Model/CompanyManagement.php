<?php

namespace Magento\Company\Model;

use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Company\Model\ResourceModel\Customer as CustomerResource;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Company\Model\Email\Sender;

/**
 * Handle various customer account actions.
 */
class CompanyManagement implements CompanyManagementInterface
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * User model factory.
     *
     * @var \Magento\User\Api\Data\UserInterfaceFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Company\Api\AclInterface
     */
    private $userRoleManagement;

    /**
     * @param CompanyRepositoryInterface $companyRepository
     * @param UserInterfaceFactory $userFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerResource $customerResource
     * @param PsrLogger $logger
     * @param Sender $companyEmailSender
     * @param \Magento\Company\Api\AclInterface $userRoleManagement
     */
    public function __construct(
        CompanyRepositoryInterface $companyRepository,
        UserInterfaceFactory $userFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerResource $customerResource,
        PsrLogger $logger,
        Sender $companyEmailSender,
        \Magento\Company\Api\AclInterface $userRoleManagement
    ) {
        $this->companyRepository = $companyRepository;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
        $this->customerResource = $customerResource;
        $this->logger = $logger;
        $this->companyEmailSender = $companyEmailSender;
        $this->userRoleManagement = $userRoleManagement;
    }

    /**
     * @inheritdoc
     */
    public function getSalesRepresentative($userId)
    {
        $salesRepresentative = '';
        if ($userId) {
            /** @var \Magento\User\Model\User $model */
            $model = $this->userFactory->create();
            $model->load($userId);
            $salesRepresentative = trim($model->getFirstName() . ' ' . $model->getLastName());
        }
        return $salesRepresentative;
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId($customerId)
    {
        $company = null;
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getExtensionAttributes() !== null
            && $customer->getExtensionAttributes()->getCompanyAttributes() !== null
            && $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        ) {
            $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            try {
                $company = $this->companyRepository->get($companyAttributes->getCompanyId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //If company is not found - just return null
            }
        }
        return $company;
    }

    /**
     * @inheritdoc
     */
    public function getAdminByCompanyId($companyId)
    {
        $companyAdmin = null;

        try {
            $company = $this->companyRepository->get($companyId);
            $companyAdmin = $this->customerRepository->getById($company->getSuperUserId());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical($e);
        }

        return $companyAdmin;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignCustomer($companyId, $customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        if ($customer->getExtensionAttributes() !== null
            && $customer->getExtensionAttributes()->getCompanyAttributes() !== null) {
            $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            $companyAttributes->setCustomerId($customerId);
            $companyAttributes->setCompanyId($companyId);
            $this->customerResource->saveAdvancedCustomAttributes($companyAttributes);
            $company = $this->companyRepository->get($companyId);
            if ($customer->getId() != $company->getSuperUserId()) {
                $this->userRoleManagement->assignUserDefaultRole($customerId, $companyId);
                $this->companyEmailSender->sendCustomerCompanyAssignNotificationEmail($customer, $companyId);
            }
        }
    }
}
