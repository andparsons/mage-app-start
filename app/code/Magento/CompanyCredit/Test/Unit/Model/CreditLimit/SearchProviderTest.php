<?php

namespace Magento\CompanyCredit\Test\Unit\Model\CreditLimit;

/**
 * Unit tests for \Magento\CompanyCredit\Model\CreditLimit\SearchProvider model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitCollectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimit\SearchProvider
     */
    private $searchProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimitCollectionFactory =
            $this->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();

        $this->searchResultsFactory = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->extensionAttributesJoinProcessor =
            $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchProvider = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditLimit\SearchProvider::class,
            [
                'creditLimitCollectionFactory'     => $this->creditLimitCollectionFactory,
                'searchResultsFactory'             => $this->searchResultsFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
            ]
        );
    }

    /**
     * Test for method getList.
     *
     * @return void
     */
    public function testGetList()
    {
        $filterField = \Magento\CompanyCredit\Api\Data\CreditLimitInterface::COMPANY_ID;
        $filterValue = 3;
        $conditionType = 'neq';
        $collectionSize = 1;
        $sortOrderField = \Magento\CompanyCredit\Api\Data\CreditLimitInterface::BALANCE;
        $sortOrderDirection = 'ASC';
        $currentPage = 2;
        $pageSize = 15;

        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $collection = $this->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\CreditLimit\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditLimitCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);
        $filterGroup = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->once())->method('getConditionType')->willReturn($conditionType);
        $filter->expects($this->once())->method('getField')->willReturn($filterField);
        $filter->expects($this->once())->method('getValue')->willReturn($filterValue);
        $collection->expects($this->once())->method('addFieldToFilter')
            ->with($filterField, [$conditionType => $filterValue])->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $searchResults->expects($this->once())->method('setTotalCount')->with($collectionSize)->willReturnSelf();
        $sortOrder = $this->getMockBuilder(\Magento\Framework\Api\SortOrder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn($sortOrderField);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn($sortOrderDirection);
        $collection->expects($this->once())->method('addOrder')
            ->with($sortOrderField, $sortOrderDirection)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $collection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $collection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Model\CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())->method('getItems')->willReturn([$creditLimit]);
        $searchResults->expects($this->once())->method('setItems')->with([$creditLimit])->willReturnSelf();
        $this->assertEquals($searchResults, $this->searchProvider->getList($searchCriteria));
    }
}
