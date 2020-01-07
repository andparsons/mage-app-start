<?php
namespace Magento\SharedCatalog\Plugin\Customer\Api;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Plugin changes company customer group id to default after a customer group was deleted.
 */
class UpdateCompanyCustomerGroupPlugin
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $catalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $moduleConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $catalogManagement
     * @param \Magento\SharedCatalog\Model\Config $moduleConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $catalogManagement,
        \Magento\SharedCatalog\Model\Config $moduleConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->companyRepository = $companyRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupManagement = $groupManagement;
        $this->companyManagement = $companyManagement;
        $this->catalogManagement = $catalogManagement;
        $this->moduleConfig = $moduleConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Change company customer group id to default after a customer group was deleted.
     *
     * @param GroupRepositoryInterface $subject
     * @param bool $result
     * @param int $groupId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, $result, $groupId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(CompanyInterface::CUSTOMER_GROUP_ID, $groupId)
            ->create();
        $companies = $this->companyRepository->getList($searchCriteria)->getItems();
        foreach ($companies as $company) {
            $company->setCustomerGroupId($this->getDefaultGroupId($company));
            $this->companyRepository->save($company);
        }

        return $result;
    }

    /**
     * Get default customer group id.
     *
     * @param CompanyInterface $company
     * @return int|null
     */
    private function getDefaultGroupId(CompanyInterface $company)
    {
        $groupId = null;
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->moduleConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            try {
                $groupId = $this->catalogManagement->getPublicCatalog()->getCustomerGroupId();
            } catch (NoSuchEntityException $e) {
                $groupId = null;
            }
        }
        if (!$groupId) {
            $companyAdmin = $this->companyManagement->getAdminByCompanyId($company->getId());
            $groupId = $this->groupManagement->getDefaultGroup($companyAdmin->getStoreId())->getId();
        }
        return $groupId;
    }
}
