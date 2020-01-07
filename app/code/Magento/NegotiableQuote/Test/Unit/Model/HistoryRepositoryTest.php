<?php

namespace Magento\NegotiableQuote\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\NegotiableQuote\Model\ResourceModel\History\CollectionFactory;

/**
 * Class HistoryRepositoryTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\History|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyResource;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyFactory;

    /**
     * @var \Magento\Framework\Api\SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryRepository
     */
    private $historyRepository;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyResource = $this->createMock(\Magento\NegotiableQuote\Model\ResourceModel\History::class);
        $this->historyFactory =
            $this->createPartialMock(\Magento\NegotiableQuote\Api\Data\HistoryInterfaceFactory::class, ['create']);
        $this->searchResultsFactory =
            $this->createPartialMock(\Magento\Framework\Api\SearchResultsFactory::class, ['create']);
        $searchResult = new \Magento\Framework\Api\SearchResults();
        $this->searchResultsFactory->expects($this->any())->method('create')->willReturn($searchResult);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\ResourceModel\History\CollectionFactory::class,
            ['create']
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyRepository = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\HistoryRepository::class,
            [
                'historyResource' => $this->historyResource,
                'historyFactory' => $this->historyFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory' => $this->collectionFactory,
                'logger' => $this->logger,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
    }

    /**
     * Test for method Save with empty ID.
     *
     * @return void
     */
    public function testSaveWithEmptyEntityId()
    {
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $this->assertEquals(false, $this->historyRepository->save($history));
    }

    /**
     * Test for method Save.
     *
     * @return void
     */
    public function testSave()
    {
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $history->expects($this->atLeastOnce())->method('getHistoryId')->willReturn(1);
        $this->assertEquals(true, $this->historyRepository->save($history));
    }

    /**
     * Test for method Save with Exception.
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveWithException()
    {
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->historyResource->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->assertEquals(false, $this->historyRepository->save($history));
    }

    /**
     * Test for method Get.
     *
     * @return void
     */
    public function testGet()
    {
        $id = 1;
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $history->expects($this->once())->method('load')->with($id)->willReturnSelf();
        $history->expects($this->atLeastOnce())->method('getHistoryId')->willReturn($id);
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->assertEquals($this->historyRepository->get($id), $history);
    }

    /**
     * Test for method Get with Exception.
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetWithException()
    {
        $id = 1;
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $history->expects($this->once())->method('load')->with($id)->willReturnSelf();
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->assertEquals($this->historyRepository->get($id), $history);
    }

    /**
     * Test for getList method.
     *
     * @dataProvider getParams
     * @param int $count
     * @param int $expectedResult
     * @return void
     */
    public function testGetList($count, $expectedResult)
    {
        $searchCriteria = $this->createMock(\Magento\Framework\Api\Search\SearchCriteria::class, null);

        $collection = $this->createMock(\Magento\NegotiableQuote\Model\ResourceModel\History\Collection::class);
        $this->collectionFactory->expects($this->any())
            ->method('create')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('getItems')->will($this->returnValue([]));
        $collection->expects($this->any())->method('getSize')->will($this->returnValue($count));

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $result = $this->historyRepository->getList($searchCriteria);
        $this->assertEquals($expectedResult, $result->getTotalCount());
    }

    /**
     * Data provider for method testGetList.
     *
     * @return array
     */
    public function getParams()
    {
        return [
            [0, 0],
            [1, 1]
        ];
    }

    /**
     * Test for delete() method.
     *
     * @return void
     */
    public function testDelete()
    {
        /** @var \Magento\NegotiableQuote\Model\History|\PHPUnit_Framework_MockObject_MockObject $history */
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $this->historyResource->expects($this->once())->method('delete');

        $this->assertEquals(true, $this->historyRepository->delete($history));
    }

    /**
     * Test for delete() method with exception.
     *
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete history log with id 1
     * @return void
     */
    public function testDeleteWithException()
    {
        /** @var \Magento\NegotiableQuote\Model\History|\PHPUnit_Framework_MockObject_MockObject $history */
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $history->expects($this->once())->method('getEntityId')->willReturn(1);
        $exception = new \Exception();
        $this->historyResource->expects($this->once())->method('delete')->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical');

        $this->historyRepository->delete($history);
    }

    /**
     * Test for deleteById() method.
     *
     * @return void
     */
    public function testDeleteById()
    {
        $id = 1;
        $history = $this->createMock(\Magento\NegotiableQuote\Model\History::class);
        $history->expects($this->once())->method('load')->with($id)->willReturnSelf();
        $history->expects($this->atLeastOnce())->method('getHistoryId')->willReturn($id);
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->historyResource->expects($this->once())->method('delete');

        $this->assertEquals(true, $this->historyRepository->deleteById($id));
    }
}
