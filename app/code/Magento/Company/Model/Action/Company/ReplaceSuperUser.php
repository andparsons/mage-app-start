<?php

namespace Magento\Company\Model\Action\Company;

/**
 * Class that replaces admin of company by another one.
 */
class ReplaceSuperUser
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\Customer\CompanyAttributes
     */
    private $companyAttributes;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Api\AclInterface
     */
    private $userRoleManagement;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\Customer\CompanyAttributes $companyAttributes
     * @param \Magento\Company\Model\Company\Structure          $companyStructure
     * @param \Magento\Company\Model\ResourceModel\Customer     $customerResource
     * @param \Magento\Company\Api\AclInterface                 $userRoleManagement
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\Customer\CompanyAttributes $companyAttributes,
        \Magento\Company\Model\Company\Structure $companyStructure,
        \Magento\Company\Model\ResourceModel\Customer $customerResource,
        \Magento\Company\Api\AclInterface $userRoleManagement
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyAttributes = $companyAttributes;
        $this->companyStructure = $companyStructure;
        $this->customerResource = $customerResource;
        $this->userRoleManagement = $userRoleManagement;
    }

    /**
     * Convert administrator of the company to user of the company, if the administrator of the company was changed.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int|null $oldSuperUser
     * @param bool $keepActive
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $oldSuperUser,
        $keepActive
    ) {
        if ($oldSuperUser && (int)$customer->getId() !== $oldSuperUser) {
            $oldCustomer = $this->customerRepository->getById($oldSuperUser);
            $companyAttributes = $this->companyAttributes->getCompanyAttributesByCustomer($customer);
            if ($companyAttributes !== null) {
                $companyId = $companyAttributes->getCompanyId();
                $companyAttributes = $oldCustomer->getExtensionAttributes()->getCompanyAttributes();
                $companyAttributes->setCustomerId($oldSuperUser);
                if (!$keepActive) {
                    $companyAttributes->setStatus(\Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_INACTIVE);
                }
                $this->customerResource->saveAdvancedCustomAttributes($companyAttributes);
                $this->userRoleManagement->assignUserDefaultRole($oldSuperUser, $companyId);
            }
            $this->copyAddressBook($oldCustomer, $customer);
            $this->companyStructure->moveStructureChildrenToParent($customer->getId());
            $this->companyStructure->removeCustomerNode($customer->getId());
            $this->companyStructure->addNode(
                $customer->getId(),
                \Magento\Company\Api\Data\StructureInterface::TYPE_CUSTOMER,
                0
            );
            $this->companyStructure->moveCustomerStructure($oldCustomer->getId(), $customer->getId(), $keepActive);
        }

        if (!$this->companyStructure->getStructureByCustomerId($customer->getId())) {
            $this->companyStructure->addNode(
                $customer->getId(),
                \Magento\Company\Api\Data\StructureInterface::TYPE_CUSTOMER,
                0
            );
        }
        return $this;
    }

    /**
     * Copy customer addresses.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $oldCustomer
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     */
    private function copyAddressBook(
        \Magento\Customer\Api\Data\CustomerInterface $oldCustomer,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        $oldAddresses = $oldCustomer->getAddresses();
        $addresses = $customer->getAddresses();
        foreach ($oldAddresses as $oldAddress) {
            $oldAddress->setId(0);
            $oldAddress->setCustomerId($customer->getId());
            if ($customer->getDefaultBilling()) {
                $oldAddress->setIsDefaultBilling(false);
            }
            if ($customer->getDefaultShipping()) {
                $oldAddress->setIsDefaultShipping(false);
            }
            $addresses[] = $oldAddress;
        }
        $customer->setAddresses($addresses);
        $this->customerRepository->save($customer);
    }
}
