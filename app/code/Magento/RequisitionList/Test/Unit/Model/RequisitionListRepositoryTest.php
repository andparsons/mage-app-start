<?php

namespace Magento\RequisitionList\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\RequisitionList\Model\RequisitionList\Items;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\CollectionFactory;

/**
 * Class RequisitionListRepositoryTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequisitionListRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListFactory;

    /**
     * @var \Magento\RequisitionList\Model\ResourceModel\RequisitionList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListResource;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var Items|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListItemRepository;

    /**
     * @var \Magento\RequisitionList\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionList;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requisitionList = $this->createMock(\Magento\RequisitionList\Model\RequisitionList::class);

        $this->requisitionListFactory = $this->createPartialMock(
            \Magento\RequisitionList\Api\Data\RequisitionListInterfaceFactory::class,
            ['create']
        );
        $this->requisitionListFactory
            ->expects($this->any())->method('create')->willReturn($this->requisitionList);
        $this->requisitionList
            ->expects($this->any())->method('load')->will($this->returnSelf());

        $this->requisitionListResource =
            $this->createMock(\Magento\RequisitionList\Model\ResourceModel\RequisitionList::class);
        $this->extensionAttributesJoinProcessor =
            $this->createMock(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class);
        $this->searchResultsFactory =
            $this->createPartialMock(\Magento\Framework\Api\SearchResultsInterfaceFactory::class, ['create']);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\RequisitionList\Model\ResourceModel\RequisitionList\CollectionFactory::class,
            ['create']
        );
        $this->requisitionListItemRepository =
            $this->createMock(\Magento\RequisitionList\Model\RequisitionList\Items::class);
        $this->moduleConfig = $this->createMock(\Magento\RequisitionList\Model\Config::class);
        $this->searchCriteriaBuilder =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListRepository = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListRepository::class,
            [
                'requisitionListFactory' => $this->requisitionListFactory,
                'requisitionListResource' => $this->requisitionListResource,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory' => $this->collectionFactory,
                'requisitionListItemRepository' => $this->requisitionListItemRepository,
                'moduleConfig' => $this->moduleConfig,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
    }

    /**
     * Test save() method
     *
     * @return void
     */
    public function testSave()
    {
        $this->mockCollection();
        $this->collection->expects($this->any())->method('getSize')->willReturnOnConsecutiveCalls(1, 1, 1, 0);
        $requisitionListItem = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $this->collection->expects($this->any())->method('getItems')->willReturn([$requisitionListItem]);
        $this->requisitionList->expects($this->any())
            ->method('getItems')
            ->willReturn([$requisitionListItem]);
        $this->requisitionList->expects($this->any())->method('getId')->willReturn(123);

        $this->requisitionListRepository->save($this->requisitionList, true);
    }

    /**
     * Test save() method with items deletion
     *
     * @return void
     */
    public function testSaveWithItemsDeletion()
    {
        $this->mockCollection();
        $this->collection->expects($this->any())->method('getSize')->willReturnOnConsecutiveCalls(1, 1, 1, 0);
        $requisitionListItem = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $this->requisitionList->expects($this->any())
            ->method('getItems')
            ->willReturnOnConsecutiveCalls([], [$requisitionListItem]);
        $this->requisitionListItemRepository->expects($this->once())
            ->method('get')
            ->willReturn($requisitionListItem);
        $this->requisitionListItemRepository->expects($this->once())->method('delete');
        $this->requisitionList->expects($this->any())->method('getId')->willReturn(123);
        $this->collection->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $this->collection->expects($this->any())->method('getItems')->willReturn([$requisitionListItem]);

        $this->requisitionListRepository->save($this->requisitionList, true);
    }

    /**
     * Test save() method with exception
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveWithException()
    {
        $this->mockCollection();
        $this->requisitionList->expects($this->any())->method('getId')->willReturn(null);

        $this->requisitionListRepository->save($this->requisitionList, true);
    }

    /**
     * Test save() method with exception from repository
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveWithExceptionFromRepository()
    {
        $this->mockCollection();
        $requisitionListItem = $this->createMock(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class);
        $this->requisitionList->expects($this->any())->method('getId')->willReturn(123);
        $this->requisitionList->expects($this->any())
            ->method('getItems')
            ->willReturn([$requisitionListItem]);
        $this->requisitionListItemRepository->expects($this->any())
            ->method('save')
            ->willThrowException(new \Exception);

        $this->requisitionListRepository->save($this->requisitionList, true);
    }

    /**
     * Mock collection for save
     *
     * @return void
     */
    private function mockCollection()
    {
        $this->collection = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\AbstractDb::class,
            [],
            '',
            false,
            false,
            true,
            ['getSize', 'getItems', 'addFieldToFilter', 'load']
        );
        $this->collectionFactory->expects($this->any())->method('create')->willReturn($this->collection);
        $this->collection->expects($this->any())->method('addFieldToFilter')->willReturnSelf();
        $this->collection->expects($this->any())->method('load')->willReturnSelf();
    }

    /**
     * Test get() method
     *
     * @return void
     */
    public function testGet()
    {
        $requisitionListId = 1;
        $this->requisitionList->expects($this->once())
            ->method('getId')
            ->willReturn($requisitionListId);
        $this->requisitionList->expects($this->once())
            ->method('load')
            ->with($requisitionListId);
        $this->requisitionListFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->requisitionList);
        $this->requisitionListRepository->get($requisitionListId);
    }

    /**
     * Test get() method with exception
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetWithException()
    {
        $requisitionListId = 1;
        $this->requisitionList->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->requisitionList->expects($this->once())
            ->method('load')
            ->with($requisitionListId);
        $this->requisitionListFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->requisitionList);
        $this->requisitionListRepository->get($requisitionListId);
    }

    /**
     * Test delete() method
     *
     * @return void
     */
    public function testDelete()
    {
        $requisitionList = $this->createPartialMock(\Magento\RequisitionList\Model\RequisitionList::class, ['getId']);
        $this->requisitionListRepository->delete($requisitionList);
    }

    /**
     * Test delete() method with exception
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @return void
     */
    public function testDeleteWithException()
    {
        $requisitionListId = 1;
        $requisitionList = $this->createPartialMock(\Magento\RequisitionList\Model\RequisitionList::class, ['getId']);
        $this->requisitionListResource->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception());
        $requisitionList->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($requisitionListId);
        $this->requisitionListRepository->delete($requisitionList);
    }

    /**
     * Test for method deleteById
     *
     * @return void
     */
    public function testDeleteById()
    {
        $requisitionListId = 3;
        $this->requisitionList->expects($this->any())->method('getId')->willReturn($requisitionListId);
        $this->requisitionList
            ->expects($this->once())
            ->method('load')
            ->with($requisitionListId)
            ->willReturn($this->requisitionList);
        $this->requisitionListResource
            ->expects($this->once())
            ->method('delete')
            ->with($this->requisitionList)
            ->willReturn(true);
        $this->assertTrue($this->requisitionListRepository->deleteById($requisitionListId));
    }
}
