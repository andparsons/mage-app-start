<?php

namespace Magento\RequisitionList\Test\Unit\Model\RequisitionList;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\CollectionFactory as ItemCollectionFactory;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;

/**
 * Class ItemsTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items
     */
    private $requisitionListItemRepository;

    /**
     * @var RequisitionListItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\RequisitionList\Model\ResourceModel\RequisitionListItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemResource;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var ItemCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemCollectionFactory;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItem;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->requisitionListItem = $this->createMock(\Magento\RequisitionList\Model\RequisitionListItem::class);

        $this->requisitionListItemFactory = $this->createPartialMock(
            \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory::class,
            ['create']
        );
        $this->requisitionListItemFactory
            ->expects($this->any())->method('create')->willReturn($this->requisitionListItem);
        $this->requisitionListItem
            ->expects($this->any())->method('load')->will($this->returnSelf());

        $this->requisitionListItemResource =
            $this->createMock(\Magento\RequisitionList\Model\ResourceModel\RequisitionListItem::class);

        $this->extensionAttributesJoinProcessor =
            $this->createMock(\Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class);

        $this->searchResultsFactory =
            $this->createPartialMock(\Magento\Framework\Api\SearchResultsInterfaceFactory::class, ['create']);
        $searchResult = new \Magento\Framework\Api\SearchResults();
        $this->searchResultsFactory->expects($this->any())->method('create')->will($this->returnValue($searchResult));

        $this->requisitionListItemCollectionFactory = $this->createPartialMock(
            \Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\CollectionFactory::class,
            ['create']
        );

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requisitionListItemRepository = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Model\RequisitionList\Items::class,
            [
                'requisitionListItemFactory' => $this->requisitionListItemFactory,
                'requisitionListItemResource' => $this->requisitionListItemResource,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory' => $this->requisitionListItemCollectionFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
    }

    /**
     * Test for method save
     */
    public function testSave()
    {
        $this->requisitionListItem->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->requisitionListItemRepository->save($this->requisitionListItem);
    }

    /**
     * Test for method save
     */
    public function testSaveWithSomeError()
    {
        $this->requisitionListItem->expects($this->any())->method('getId')->willReturn(1);
        $this->requisitionListItemResource
            ->expects($this->any())
            ->method('save')
            ->willThrowException(new \Exception());
        try {
            $this->requisitionListItemRepository->save($this->requisitionListItem);
        } catch (\Exception $e) {
            $this->assertEquals(__('Could not save Requisition List'), $e->getMessage());
        }
    }

    /**
     * Test for method get
     */
    public function testGet()
    {
        $this->requisitionListItem->expects($this->any())->method('getId')->willReturn(1);
        $this->assertEquals($this->requisitionListItem, $this->requisitionListItemRepository->get(1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetIfRequisitionListIsNotFound()
    {
        $this->requisitionListItem->expects($this->any())->method('getId')->willReturn(0);
        $this->assertNull($this->requisitionListItemRepository->get(1));
    }

    /**
     * Test for method delete
     */
    public function testDelete()
    {
        $this->requisitionListItem->expects($this->once())->method('getId')->willReturn(2);
        $this->requisitionListItemResource
            ->expects($this->once())
            ->method('delete')
            ->with($this->requisitionListItem)
            ->willReturn(true);
        $this->assertTrue($this->requisitionListItemRepository->delete($this->requisitionListItem));
    }

    /**
     * Test for method delete
     */
    public function testDeleteWithError()
    {
        $requisitionListItemId = 2;
        $exception = new \Magento\Framework\Exception\StateException(
            new \Magento\Framework\Phrase('Cannot delete Requisition List with id %1', [$requisitionListItemId])
        );
        $this->requisitionListItem->expects($this->any())->method('getId')->willReturn($requisitionListItemId);
        $this->requisitionListItemResource
            ->expects($this->any())
            ->method('delete')
            ->willThrowException(new \Exception());

        try {
            $this->requisitionListItemRepository->delete($this->requisitionListItem);
        } catch (\Exception $e) {
            $this->assertEquals(
                $e->getMessage(),
                $exception->getMessage()
            );
        }
    }

    /**
     * Test for method deleteById
     */
    public function testDeleteById()
    {
        $requisitionListItemId = 3;
        $this->requisitionListItem->expects($this->any())->method('getId')->willReturn($requisitionListItemId);
        $this->requisitionListItem
            ->expects($this->once())
            ->method('load')
            ->with($requisitionListItemId)
            ->willReturn($this->requisitionListItem);
        $this->requisitionListItemResource
            ->expects($this->once())
            ->method('delete')
            ->with($this->requisitionListItem)
            ->willReturn(true);
        $this->assertTrue($this->requisitionListItemRepository->deleteById($requisitionListItemId));
    }

    /**
     * Test for method getList
     * @dataProvider getParamsForModel
     *
     * @param $count
     * @param $expectedResult
     */
    public function testGetList($count, $expectedResult)
    {
        $searchCriteria = $this->createMock(
            \Magento\Framework\Api\Search\SearchCriteria::class,
            null
        );

        $collection =
            $this->createMock(\Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item\Collection::class);
        $this->requisitionListItemCollectionFactory->expects($this->any())
            ->method('create')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('getItems')->will($this->returnValue([]));
        $collection->expects($this->any())->method('getSize')->will($this->returnValue($count));

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $result = $this->requisitionListItemRepository->getList($searchCriteria);
        $this->assertEquals($expectedResult, $result->getTotalCount());
    }

    /**
     * Data provider for method testGetList
     * @return array
     */
    public function getParamsForModel()
    {
        return [
            [0, 0],
            [1, 1]
        ];
    }
}
