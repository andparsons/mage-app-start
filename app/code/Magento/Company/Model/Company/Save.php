<?php

namespace Magento\Company\Model\Company;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class responsible for creating and updating company entities.
 */
class Save
{
    /**
     * @var \Magento\Company\Model\SaveHandlerPool
     */
    private $saveHandlerPool;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Model\SaveValidatorPool
     */
    private $saveValidatorPool;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    private $userCollectionFactory;

    /**
     * @param \Magento\Company\Model\SaveHandlerPool $saveHandlerPool
     * @param \Magento\Company\Model\ResourceModel\Company $companyResource
     * @param \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory
     * @param \Magento\Company\Model\SaveValidatorPool $saveValidatorPool
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function __construct(
        \Magento\Company\Model\SaveHandlerPool $saveHandlerPool,
        \Magento\Company\Model\ResourceModel\Company $companyResource,
        \Magento\Company\Api\Data\CompanyInterfaceFactory $companyFactory,
        \Magento\Company\Model\SaveValidatorPool $saveValidatorPool,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
    ) {
        $this->saveHandlerPool = $saveHandlerPool;
        $this->companyResource = $companyResource;
        $this->companyFactory = $companyFactory;
        $this->saveValidatorPool = $saveValidatorPool;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    /**
     * Checks if provided data for a company is correct, saves the company entity and executes additional save handlers
     * from the pool.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        $this->processAddress($company);
        $this->processSalesRepresentative($company);
        $companyId = $company->getId();
        $initialCompany = $this->getInitialCompany($companyId);
        $this->saveValidatorPool->execute($company, $initialCompany);
        try {
            $this->companyResource->save($company);
            $this->saveHandlerPool->execute($company, $initialCompany);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save company'),
                $e
            );
        }

        return $company;
    }

    /**
     * Get initial company.
     *
     * @param int|null $companyId
     * @return \Magento\Company\Api\Data\CompanyInterface
     */
    private function getInitialCompany($companyId)
    {
        $company = $this->companyFactory->create();
        try {
            $this->companyResource->load($company, $companyId);
        } catch (\Exception $e) {
            //Do nothing, just leave the object blank.
        }

        return $company;
    }

    /**
     * Set default sales representative (admin user responsible for company) if it is not set.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return void
     */
    private function processSalesRepresentative(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        if (!$company->getSalesRepresentativeId()) {
            /** @var \Magento\User\Model\ResourceModel\User\Collection $userCollection */
            $userCollection = $this->userCollectionFactory->create();
            $company->setSalesRepresentativeId($userCollection->setPageSize(1)->getFirstItem()->getId());
        }
    }

    /**
     * Prepare company address.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return void
     */
    private function processAddress(\Magento\Company\Api\Data\CompanyInterface $company)
    {
        if (!$company->getRegionId()) {
            $company->setRegionId(null);
        } else {
            $company->setRegion(null);
        }
        $street = $company->getStreet();
        if (is_array($street) && count($street)) {
            $company->setStreet(trim(implode("\n", $street)));
        }
    }
}
