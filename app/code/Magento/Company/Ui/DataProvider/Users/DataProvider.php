<?php
namespace Magento\Company\Ui\DataProvider\Users;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Company\Model\Company\StructureFactory;

/**
 * Class DataProvider.
 *
 * @api
 * @since 100.0.0
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Tree structure.
     *
     * @var \Magento\Company\Model\Company\StructureFactory
     */
    private $structureFactory;

    /**
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface
     */
    private $roleManagement;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission
     */
    private $companyAdminPermission;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param StructureFactory $structureFactory
     * @param \Magento\Company\Model\CompanyUser $companyUser
     * @param \Magento\Company\Api\RoleManagementInterface $roleManagement
     * @param \Magento\Company\Model\CompanyAdminPermission $companyAdminPermission
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        StructureFactory $structureFactory,
        \Magento\Company\Model\CompanyUser $companyUser,
        \Magento\Company\Api\RoleManagementInterface $roleManagement,
        \Magento\Company\Model\CompanyAdminPermission $companyAdminPermission,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->structureFactory = $structureFactory;
        $this->companyUser = $companyUser;
        $this->roleManagement = $roleManagement;
        $this->companyAdminPermission = $companyAdminPermission;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        try {
            $searchResult = $this->getSearchResult();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->createEmptySearchResult();
        }

        return $this->prepareSearchResult($searchResult);
    }

    /**
     * Create empty search result.
     *
     * @return array
     */
    private function createEmptySearchResult()
    {
        $arrItems = [];
        $arrItems['items'] = [];
        $arrItems['totalRecords'] = 0;
        return $arrItems;
    }

    /**
     * Returns Search result.
     *
     * @return SearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchResult()
    {
        $companyId = $this->companyUser->getCurrentCompanyId();
        $this->filterBuilder->setField('company_customer.company_id');
        if ($companyId == 0) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        } else {
            $this->filterBuilder->setConditionType('eq')
                ->setValue($companyId);
        }
        $filter = $this->filterBuilder->create();
        $this->searchCriteriaBuilder->addFilter($filter);

        return parent::getSearchResult();
    }

    /**
     * Prepare search result.
     *
     * @param SearchResultInterface $searchResult
     * @return array
     */
    private function prepareSearchResult(SearchResultInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['items'] = [];

        foreach ($searchResult->getItems() as $item) {
            $itemData = [];

            foreach ($item->getCustomAttributes() as $attribute) {
                $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
            }
            if ($this->companyAdminPermission->isGivenUserCompanyAdmin($item->getId())) {
                $itemData['role_id'] = $this->roleManagement->getCompanyAdminRoleId();
                $roleName = $this->roleManagement->getCompanyAdminRoleName();
                $itemData['role_name'] = __($roleName);
            }

            $itemData['team'] = $this->getTeamName($item->getId());
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    /**
     * Get team name.
     *
     * @param int $customerId
     * @return null|string
     */
    private function getTeamName($customerId)
    {
        /**
         * @var \Magento\Company\Model\Company\Structure $structure
         */
        $structure = $this->structureFactory->create();
        return $structure->getTeamNameByCustomerId($customerId);
    }
}
