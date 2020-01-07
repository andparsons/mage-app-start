<?php
namespace Magento\SharedCatalog\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Shared catalog companies actions.
 */
class CompanyManagement implements \Magento\SharedCatalog\Api\CompanyManagementInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Management
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog
     */
    private $resource;

    /**
     * CompanyManagement constructor.
     *
     * @param \Magento\SharedCatalog\Model\Management $sharedCatalogManagement
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog $resource
     */
    public function __construct(
        \Magento\SharedCatalog\Model\Management $sharedCatalogManagement,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog $resource
    ) {
        $this->sharedCatalogManagement = $sharedCatalogManagement;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->companyRepository = $companyRepository;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanies($sharedCatalogId)
    {
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $builder = $this->searchCriteriaBuilder->addFilter('customer_group_id', $sharedCatalog->getCustomerGroupId());
        $companies = $this->companyRepository->getList($builder->create())->getItems();

        return json_encode($this->prepareCompanyIds($companies));
    }

    /**
     * {@inheritdoc}
     */
    public function assignCompanies($sharedCatalogId, array $companies)
    {
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $customerGroupId = $sharedCatalog->getCustomerGroupId();
        $companyIds = $this->prepareCompanyIds($companies);
        $builder = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $companyIds, 'in')
            ->addFilter('customer_group_id', $customerGroupId, 'neq');
        $companies = $this->companyRepository->getList($builder->create())->getItems();

        if ($companies) {
            $this->saveCustomerGroup($companies, $customerGroupId);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unassignCompanies($sharedCatalogId, array $companies)
    {
        if (empty($companies)) {
            return true;
        }
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $publicCatalogCustomerGroupId = $this->sharedCatalogManagement->getPublicCatalog()->getCustomerGroupId();
        $currentCustomerGroupId = $sharedCatalog->getCustomerGroupId();

        if ($currentCustomerGroupId == $publicCatalogCustomerGroupId) {
            throw new LocalizedException(__('You cannot unassign a company from the public shared catalog.'));
        }

        $companyIds = $this->prepareCompanyIds($companies);
        $builder = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $companyIds, 'in')
            ->addFilter('customer_group_id', $currentCustomerGroupId);
        $companies = $this->companyRepository->getList($builder->create())->getItems();

        if ($companies) {
            $this->saveCustomerGroup($companies, $publicCatalogCustomerGroupId);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unassignAllCompanies($sharedCatalogId)
    {
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $publicCatalogCustomerGroupId = $this->sharedCatalogManagement->getPublicCatalog()->getCustomerGroupId();
        $builder = $this->searchCriteriaBuilder->addFilter('customer_group_id', $sharedCatalog->getCustomerGroupId());
        $companies = $this->companyRepository->getList($builder->create())->getItems();

        if ($companies) {
            $this->saveCustomerGroup($companies, $publicCatalogCustomerGroupId);
        }

        return true;
    }

    /**
     * Save companies customer group.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface[] $companies
     * @param int $customerGroupId
     * @return void
     * @throws \Exception
     */
    private function saveCustomerGroup(array $companies, $customerGroupId)
    {
        $this->resource->beginTransaction();
        try {
            /** @var \Magento\Company\Api\Data\CompanyInterface $company */
            foreach ($companies as $company) {
                $company->setCustomerGroupId($customerGroupId);
                $this->companyRepository->save($company);
            }
            $this->resource->commit();
        } catch (\Exception $e) {
            $this->resource->rollBack();
            throw $e;
        }
    }

    /**
     * Prepare company ids.
     *
     * @param \Magento\Company\Api\Data\CompanyInterface[] $companies
     * @return array
     */
    private function prepareCompanyIds(array $companies)
    {
        $companyIds = [];

        /** @var \Magento\Company\Api\Data\CompanyInterface $company */
        foreach ($companies as $company) {
            $companyIds[] = $company->getId();
        }

        return $companyIds;
    }
}
