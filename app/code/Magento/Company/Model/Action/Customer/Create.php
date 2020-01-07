<?php

namespace Magento\Company\Model\Action\Customer;

/**
 * Class that creates a customer and assigns it to company
 */
class Create
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerManager;
    
    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface  $customerManager
     * @param \Magento\Company\Model\Company\Structure $structureManager
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $customerManager,
        \Magento\Company\Model\Company\Structure $structureManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerManager = $customerManager;
        $this->structureManager = $structureManager;
    }

    /**
     * Create a customer and assigns it to the company
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $targetId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(\Magento\Customer\Api\Data\CustomerInterface $customer, $targetId)
    {
        if ($customer->getId()) {
            $this->customerRepository->save($customer);
        } else {
            $this->customerManager->createAccount($customer);
        }
        /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customer */
        $customer = $this->customerRepository->get($customer->getEmail());
        $this->addCustomerToStructure($customer, $targetId);

        return $customer;
    }

    /**
     * Add customer to structure.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $targetId
     * @return void
     */
    private function addCustomerToStructure(\Magento\Customer\Api\Data\CustomerInterface $customer, $targetId)
    {
        $structure = $this->structureManager->getStructureByCustomerId($customer->getId());
        if ($structure && $targetId && $structure->getId()) {
            $this->structureManager->removeCustomerNode($customer->getId());
            $this->structureManager->addNode(
                $customer->getId(),
                \Magento\Company\Api\Data\StructureInterface::TYPE_CUSTOMER,
                $targetId
            );
        }
    }
}
