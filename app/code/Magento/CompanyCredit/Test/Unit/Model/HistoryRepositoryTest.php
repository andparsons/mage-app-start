<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class HistoryRepositoryTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyResource;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyCollectionFactory;

    /**
     * @var \Magento\CompanyCredit\Model\HistorySearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Email\Sender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailSender;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepository
     */
    private $historyRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyFactory = $this->createPartialMock(
            \Magento\CompanyCredit\Model\HistoryFactory::class,
            ['create']
        );
        $this->historyResource = $this->createMock(
            \Magento\CompanyCredit\Model\ResourceModel\History::class
        );
        $this->historyCollectionFactory = $this->createPartialMock(
            \Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory::class,
            ['create']
        );
        $this->searchResultsFactory = $this->createPartialMock(
            \Magento\Framework\Api\SearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->emailSender = $this->createMock(
            \Magento\CompanyCredit\Model\Email\Sender::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyRepository = $objectManager->getObject(
            \Magento\CompanyCredit\Model\HistoryRepository::class,
            [
                'historyFactory' => $this->historyFactory,
                'historyResource' => $this->historyResource,
                'historyCollectionFactory' => $this->historyCollectionFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'emailSender' => $this->emailSender,
            ]
        );
    }

    /**
     * Test for method save.
     *
     * @return void
     */
    public function testSave()
    {
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $this->historyResource->expects($this->once())->method('save')->with($history)->willReturnSelf();
        $this->emailSender->expects($this->once())
            ->method('sendCompanyCreditChangedNotificationEmail')
            ->with($history)
            ->willReturnSelf();
        $this->assertEquals($history, $this->historyRepository->save($history));
    }

    /**
     * Test for method save with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save history
     */
    public function testSaveWithException()
    {
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $this->historyResource->expects($this->once())->method('save')->with($history)
            ->willThrowException(new \Exception('Exception message'));
        $this->historyRepository->save($history);
    }

    /**
     * Test for method get.
     *
     * @return void
     */
    public function testGet()
    {
        $historyId = 1;
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->historyResource->expects($this->once())->method('load')->with($history, $historyId)->willReturnSelf();
        $history->expects($this->once())->method('getId')->willReturn($historyId);
        $this->assertEquals($history, $this->historyRepository->get($historyId));
    }

    /**
     * Test for method get with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetWithException()
    {
        $historyId = 1;
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->historyResource->expects($this->once())->method('load')->with($history, $historyId)->willReturnSelf();
        $history->expects($this->once())->method('getId')->willReturn(null);
        $this->assertEquals($history, $this->historyRepository->get($historyId));
    }

    /**
     * Test for method delete.
     *
     * @return void
     */
    public function testDelete()
    {
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $history->expects($this->once())->method('getId')->willReturn(1);
        $this->historyResource->expects($this->once())->method('delete')->with($history)->willReturnSelf();
        $this->assertTrue($this->historyRepository->delete($history));
    }

    /**
     * Test for method delete with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Cannot delete history with id 1
     */
    public function testDeleteWithException()
    {
        $history = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $history->expects($this->exactly(2))->method('getId')->willReturn(1);
        $this->historyResource->expects($this->once())->method('delete')->with($history)
            ->willThrowException(new \Exception('Exception message'));
        $this->historyRepository->delete($history);
    }

    /**
     * Test for method getList.
     *
     * @return void
     */
    public function testGetList()
    {
        $filterField = \Magento\CompanyCredit\Model\HistoryInterface::TYPE;
        $filterValue = \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REIMBURSED;
        $conditionType = 'neq';
        $collectionSize = 1;
        $sortOrderField = \Magento\CompanyCredit\Model\HistoryInterface::DATETIME;
        $sortOrderDirection = 'DESC';
        $currentPage = 2;
        $pageSize = 15;

        $searchCriteria = $this->createMock(
            \Magento\Framework\Api\Search\SearchCriteriaInterface::class
        );
        $searchResults = $this->createMock(\Magento\Framework\Api\Search\SearchResultInterface::class);
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $collection = $this->createMock(
            \Magento\CompanyCredit\Model\ResourceModel\History\Collection::class
        );
        $this->historyCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $filterGroup = $this->createMock(\Magento\Framework\Api\Search\FilterGroup::class);
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->once())->method('getConditionType')->willReturn($conditionType);
        $filter->expects($this->once())->method('getField')->willReturn($filterField);
        $filter->expects($this->once())->method('getValue')->willReturn($filterValue);
        $collection->expects($this->once())->method('addFieldToFilter')
            ->with($filterField, [$conditionType => $filterValue])->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $searchResults->expects($this->once())->method('setTotalCount')->with($collectionSize)->willReturnSelf();
        $sortOrder = $this->createMock(\Magento\Framework\Api\SortOrder::class);
        $searchCriteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn($sortOrderField);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn($sortOrderDirection);
        $collection->expects($this->once())->method('addOrder')
            ->with($sortOrderField, $sortOrderDirection)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $collection->expects($this->once())->method('setCurPage')->with($currentPage)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $collection->expects($this->once())->method('setPageSize')->with($pageSize)->willReturnSelf();
        $creditLimit = $this->createMock(\Magento\CompanyCredit\Model\CreditLimit::class);
        $collection->expects($this->once())->method('getItems')->willReturn([$creditLimit]);
        $searchResults->expects($this->once())->method('setItems')->with([$creditLimit])->willReturnSelf();
        $this->assertEquals($searchResults, $this->historyRepository->getList($searchCriteria));
    }
}
