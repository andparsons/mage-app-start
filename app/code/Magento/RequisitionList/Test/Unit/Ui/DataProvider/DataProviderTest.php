<?php

namespace Magento\RequisitionList\Test\Unit\Ui\DataProvider;

/**
 * Class DataProviderTest
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\DataProvider\Reporting|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reporting;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerContext;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteria;

    /**
     * @var \Magento\RequisitionList\Ui\DataProvider\DataProvider
     */
    private $dataProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->reporting = $this->createMock(\Magento\Framework\View\Element\UiComponent\DataProvider\Reporting::class);
        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->filterBuilder = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->customerContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->requisitionListRepository =
            $this->createMock(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class);
        $this->searchCriteria = $this->createMock(\Magento\Framework\Api\Search\SearchCriteria::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\RequisitionList\Ui\DataProvider\DataProvider::class,
            [
                'searchCriteria' => $this->searchCriteria,
                'reporting' => $this->reporting,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'request' => $this->request,
                'filterBuilder' => $this->filterBuilder,
                'customerContext' => $this->customerContext,
                'requisitionListRepository' => $this->requisitionListRepository,
                'meta' => [],
                'data' => []
            ]
        );
    }

    /**
     * Test getData
     */
    public function testGetData()
    {
        $searchResultItem =
            $this->createPartialMock(\Magento\Framework\Api\ExtensibleDataInterface::class, ['getData']);
        $searchResultItem->expects($this->any())->method('getData')->willReturn(['a' => 'value 1', 'b' => 'value 2']);
        $searchResult = $this->getSearchResult();
        $searchResult->expects($this->any())->method('getTotalCount')->willReturn(1);
        $searchResult->expects($this->any())->method('getItems')->willReturn([$searchResultItem]);
        $items = [
            'totalRecords' => 1,
            'items' => [
                [
                    'a' => 'value 1',
                    'b' => 'value 2'
                ]
            ]
        ];

        $this->assertEquals($items, $this->dataProvider->getData());
    }

    /**
     * Test getSearchResult
     */
    public function testGetSearchResult()
    {
        $this->getSearchResult();

        $this->assertInstanceOf(
            \Magento\Framework\Api\SearchResultsInterface::class,
            $this->dataProvider->getSearchResult()
        );
    }

    /**
     * Prepare getSearchResult mocks
     *
     * @return \Magento\Framework\Api\SearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSearchResult()
    {
        $this->customerContext->expects($this->any())->method('getUserId')->willReturn(1);
        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilder->expects($this->any())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->any())->method('setConditionType')->willReturnSelf();
        $this->filterBuilder->expects($this->any())->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->any())->method('create')->willReturn($filter);
        $this->searchCriteria->expects($this->any())->method('setRequestName')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($this->searchCriteria);
        $searchResult = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->requisitionListRepository->expects($this->any())->method('getList')->willReturn($searchResult);

        return $searchResult;
    }
}
