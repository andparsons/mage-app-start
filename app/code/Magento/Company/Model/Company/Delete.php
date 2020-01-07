<?php

namespace Magento\Company\Model\Company;

/**
 * Class for deleting a company entity.
 */
class Delete
{
    /**
     * @var int
     */
    private $noCompanyId = 0;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Model\Company\Structure
     */
    private $structureManager;

    /**
     * @var \Magento\Company\Api\TeamRepositoryInterface
     */
    private $teamRepository;

    /**
     * @var \Magento\Company\Model\StructureRepository
     */
    private $structureRepository;

    /**
     * @param \Magento\Company\Model\ResourceModel\Company $companyResource
     * @param \Magento\Company\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Company\Model\Company\Structure $structureManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\TeamRepositoryInterface $teamRepository
     * @param \Magento\Company\Model\StructureRepository $structureRepository
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Company $companyResource,
        \Magento\Company\Model\ResourceModel\Customer $customerResource,
        \Magento\Company\Model\Company\Structure $structureManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\TeamRepositoryInterface $teamRepository,
        \Magento\Company\Model\StructureRepository $structureRepository
    ) {
        $this->companyResource = $companyResource;
        $this->customerResource = $customerResource;
        $this->structureManager = $structureManager;
        $this->customerRepository = $customerRepository;
        $this->teamRepository = $teamRepository;
        $this->structureRepository = $structureRepository;
    }

    /**
     * Detaches customer entities from company entity and deletes it.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function delete(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $allowedIds = $this->structureManager->getAllowedIds($company->getSuperUserId());
        $teams = $this->structureManager->getUserChildTeams($company->getSuperUserId());
        $this->companyResource->delete($company);
        $this->detachCustomersFromCompany($allowedIds['users']);
        $this->deleteTeams($teams);
    }

    /**
     * Delete company teams.
     *
     * @param \Magento\Company\Api\Data\StructureInterface[] $teams
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function deleteTeams(array $teams)
    {
        foreach ($teams as $teamStructure) {
            $this->teamRepository->deleteById($teamStructure->getEntityId());
            $this->structureRepository->delete($teamStructure);
        }
    }

    /**
     * Detach customers from the company.
     *
     * @param array $users
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function detachCustomersFromCompany(array $users)
    {
        foreach ($users as $customerId) {
            $this->structureManager->removeCustomerNode($customerId);
            $this->detachCustomerFromCompany($customerId);
        }
    }

    /**
     * Detach the customer from the company.
     *
     * @param int $customerId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function detachCustomerFromCompany($customerId)
    {
        $customer = $this->customerRepository->getById($customerId);
        /** @var \Magento\Company\Api\Data\CompanyCustomerInterface $companyAttributes */
        $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
        $companyAttributes->setCompanyId($this->noCompanyId);
        $companyAttributes->setStatus(\Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_INACTIVE);
        $this->customerRepository->save($customer);
    }
}
