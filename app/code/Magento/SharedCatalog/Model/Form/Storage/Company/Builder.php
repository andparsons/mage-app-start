<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage\Company;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\SharedCatalog\Api\SharedCatalogManagementInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\Form\Storage\Company as CompanyStorage;

/**
 * Class Company Storage Builder
 */
class Builder
{
    /**
     * @var SharedCatalogManagementInterface
     */
    protected $catalogManagement;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param SharedCatalogManagementInterface $catalogManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        SharedCatalogManagementInterface $catalogManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CompanyRepositoryInterface $companyRepository
    ) {
        $this->catalogManagement = $catalogManagement;
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param CompanyStorage $companyStorage
     * @param SharedCatalogInterface $sharedCatalog
     * @return CompanyStorage
     */
    public function build(CompanyStorage $companyStorage, SharedCatalogInterface $sharedCatalog)
    {
        $builder = $this->searchCriteriaBuilder->addFilter('customer_group_id', $sharedCatalog->getCustomerGroupId());
        $companies = $this->companyRepository->getList($builder->create())->getItems();

        $companyStorage->setSharedCatalogId($sharedCatalog->getId());
        $companyStorage->setDefaultCatalogId($this->catalogManagement->getPublicCatalog()->getId());
        $companyStorage->setCompanies(array_keys($companies));

        return $companyStorage;
    }
}
