<?php
namespace Magento\RequisitionList\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;

/**
 * @api
 * @since 100.0.0
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var UserContextInterface
     */
    private $customerContext;

    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * DataProvider constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param UserContextInterface $customerContext
     * @param RequisitionListRepositoryInterface $requisitionListRepository
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
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        UserContextInterface $customerContext,
        RequisitionListRepositoryInterface $requisitionListRepository,
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
        $this->requisitionListRepository = $requisitionListRepository;
        $this->customerContext = $customerContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->formatOutput($this->getSearchResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchResult()
    {
        $this->addOrder('name', 'ASC');
        $customerId = $this->customerContext->getUserId();
        $filter = $this->filterBuilder
            ->setField('main_table.customer_id')
            ->setConditionType('eq')
            ->setValue($customerId)
            ->create();
        $this->searchCriteriaBuilder->addFilter($filter);
        $this->searchCriteria = $this->searchCriteriaBuilder->create();
        $this->searchCriteria->setRequestName($this->name);

        return $this->requisitionListRepository->getList($this->getSearchCriteria());
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
            $arrItems['items'][] = $itemData;
        }
        return $arrItems;
    }
}
