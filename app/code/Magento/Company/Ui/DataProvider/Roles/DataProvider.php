<?php
namespace Magento\Company\Ui\DataProvider\Roles;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Class DataProvider
 *
 * @api
 * @since 100.0.0
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var \Magento\Company\Model\RoleRepository
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Model\UserRoleManagement
     */
    private $userRoleManagement;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param \Magento\Company\Model\RoleRepository $roleRepository
     * @param \Magento\Company\Model\CompanyUser $companyUser
     * @param \Magento\Company\Model\UserRoleManagement $userRoleManagement
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Company\Model\RoleRepository $roleRepository,
        \Magento\Company\Model\CompanyUser $companyUser,
        \Magento\Company\Model\UserRoleManagement $userRoleManagement,
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
        $this->roleRepository = $roleRepository;
        $this->companyUser = $companyUser;
        $this->userRoleManagement = $userRoleManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->formatOutput($this->getSearchResult());
    }

    /**
     * Returns Search result
     *
     * @return SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSearchResult()
    {
        $this->addOrder('role_name', 'ASC');
        $filter = $this->filterBuilder
            ->setField('main_table.company_id')
            ->setConditionType('eq')
            ->setValue($this->companyUser->getCurrentCompanyId())
            ->create();
        $this->searchCriteriaBuilder->addFilter($filter);
        $this->searchCriteria = $this->searchCriteriaBuilder->create();
        $this->searchCriteria->setRequestName($this->name);

        return $this->roleRepository->getList($this->getSearchCriteria(), true);
    }

    /**
     * @param SearchResultsInterface $searchResult
     * @return array
     */
    private function formatOutput(SearchResultsInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            foreach ($item->getData() as $key => $value) {
                $itemData[$key] = $value;
            }
            $roleId = $item->getRoleId();
            $itemData['users_count'] = $this->userRoleManagement->getUsersCountByRoleId($roleId);
            $arrItems['items'][] = $itemData;
        }
        return $arrItems;
    }
}
