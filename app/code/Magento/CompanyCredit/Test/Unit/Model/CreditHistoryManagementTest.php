<?php
namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Magento\CompanyCredit\Model\ResourceModel\History\Collection as HistoryCollection;

/**
 * Unit tests for CreditHistoryManagement model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditHistoryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\CompanyCredit\Model\CreditHistoryManagement
     */
    private $creditHistoryManagement;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyResourceMock;

    /**
     * @var HistoryCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyCollectionFactoryMock;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepositoryMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->historyResourceMock = $this->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\History::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->historyCollectionFactoryMock = $this->getMockBuilder(HistoryCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultsFactoryMock = $this
            ->getMockBuilder(\Magento\Framework\Api\SearchResultsInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->historyRepositoryMock = $this
            ->getMockBuilder(\Magento\CompanyCredit\Model\HistoryRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->creditHistoryManagement = $this->objectManagerHelper->getObject(
            \Magento\CompanyCredit\Model\CreditHistoryManagement::class,
            [
                'historyResource' => $this->historyResourceMock,
                'historyCollectionFactory' => $this->historyCollectionFactoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'historyRepository' => $this->historyRepositoryMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test for update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $historyId = 1;
        $purchaseOrder = 'PO-001';
        $comment = 'History comment';
        $historyComments = ['system' => 'System comment'];
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyRepositoryMock->expects($this->once())->method('get')->with($historyId)->willReturn($history);
        $history->expects($this->once())->method('getType')
            ->willReturn(\Magento\CompanyCredit\Model\HistoryInterface::TYPE_REIMBURSED);
        $history->expects($this->atLeastOnce())->method('setPurchaseOrder')->with($purchaseOrder)->willReturnSelf();
        $history->expects($this->atLeastOnce())->method('getComment')->willReturn(json_encode($historyComments));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')->with(json_encode($historyComments))->willReturn($historyComments);
        $this->serializerMock->expects($this->once())->method('serialize')
            ->with($historyComments + ['custom' => $comment])
            ->willReturn(json_encode($historyComments + ['custom' => $comment]));
        $history->expects($this->once())
            ->method('setComment')->with(json_encode($historyComments + ['custom' => $comment]))->willReturnSelf();
        $this->historyResourceMock->expects($this->once())->method('save')->with($history)->willReturn($history);
        $this->assertTrue($this->creditHistoryManagement->update($historyId, $purchaseOrder, $comment));
    }

    /**
     * Test for update method with save exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not update history
     */
    public function testUpdateWithSaveException()
    {
        $historyId = 1;
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyRepositoryMock->expects($this->once())->method('get')->with($historyId)->willReturn($history);
        $history->expects($this->once())->method('getType')
            ->willReturn(\Magento\CompanyCredit\Model\HistoryInterface::TYPE_REIMBURSED);
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $this->historyResourceMock->expects($this->once())
            ->method('save')->with($history)->willThrowException(
                new \Magento\Framework\Exception\CouldNotSaveException($phrase)
            );
        $this->creditHistoryManagement->update($historyId);
    }

    /**
     * Test for update method with wrong type.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Cannot process the request. Please check the operation type and try again.
     */
    public function testUpdateWithWrongType()
    {
        $historyId = 1;
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyRepositoryMock->expects($this->once())->method('get')->with($historyId)->willReturn($history);
        $history->expects($this->once())->method('getType')
            ->willReturn(\Magento\CompanyCredit\Model\HistoryInterface::TYPE_ALLOCATED);
        $this->creditHistoryManagement->update($historyId);
    }

    /**
     * Test for getList method.
     *
     * @return void
     */
    public function testGetList()
    {
        $filterField = 'entity_id';
        $filterValue = 1;
        $sortField = 'datetime';
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $searchResults = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchResultsFactoryMock->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $collection = $this->getMockBuilder(\Magento\CompanyCredit\Model\ResourceModel\History\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collection);
        $filterGroup = $this->getMockBuilder(\Magento\Framework\Api\Search\FilterGroup::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroup]);
        $filter = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()->getMock();
        $filterGroup->expects($this->once())->method('getFilters')->willReturn([$filter]);
        $filter->expects($this->once())->method('getConditionType')->willReturn(null);
        $filter->expects($this->once())->method('getField')->willReturn($filterField);
        $filter->expects($this->once())->method('getValue')->willReturn($filterValue);
        $collection->expects($this->once())
            ->method('addFieldToFilter')->with($filterField, ['eq' => $filterValue])->willReturnSelf();
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $searchResults->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();
        $sortOrder = $this->getMockBuilder(\Magento\Framework\Api\SortOrder::class)
            ->disableOriginalConstructor()->getMock();
        $searchCriteria->expects($this->once())->method('getSortOrders')->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())->method('getField')->willReturn($sortField);
        $sortOrder->expects($this->once())->method('getDirection')->willReturn(null);
        $collection->expects($this->once())->method('addOrder')->with($sortField)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getCurrentPage')->willReturn(2);
        $collection->expects($this->once())->method('setCurPage')->with(2)->willReturnSelf();
        $searchCriteria->expects($this->once())->method('getPageSize')->willReturn(20);
        $collection->expects($this->once())->method('setPageSize')->with(20)->willReturnSelf();
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\HistoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('getItems')->willReturn([$history]);
        $searchResults->expects($this->once())->method('setItems')->with([$history])->willReturnSelf();
        $this->assertEquals($searchResults, $this->creditHistoryManagement->getList($searchCriteria));
    }
}
