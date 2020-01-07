<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Comment;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

/**
 * Test for \Magento\NegotiableQuote\Model\Comment\SearchProvider class
 */
class SearchProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var CommentCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Comment\SearchProvider
     */
    private $searchProvider;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $comment = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $comment->expects($this->any())->method('load')->will($this->returnSelf());
        $comment->expects($this->any())->method('getId')->willReturn(14);
        $comment->expects($this->any())->method('delete')->will($this->returnSelf());

        $this->searchResultsFactory = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $searchResult = new \Magento\Framework\Api\SearchResults();
        $this->searchResultsFactory->expects($this->any())->method('create')->will($this->returnValue($searchResult));

        $this->collectionFactory =
            $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Comment\CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchProvider = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Comment\SearchProvider::class,
            [
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory' => $this->collectionFactory,
                'collectionProcessor' => $this->collectionProcessorMock,
            ]
        );
    }

    /**
     * Test for method \Magento\NegotiableQuote\Model\Comment\SearchProvider::getList
     * @dataProvider getParamsForModel
     *
     * @param $count
     * @param $expectedResult
     * @return void
     */
    public function testGetList($count, $expectedResult)
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\NegotiableQuote\Model\ResourceModel\Comment\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->any())
            ->method('create')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('getItems')->will($this->returnValue([]));
        $collection->expects($this->any())->method('getSize')->will($this->returnValue($count));

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $result = $this->searchProvider->getList($searchCriteria);
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
