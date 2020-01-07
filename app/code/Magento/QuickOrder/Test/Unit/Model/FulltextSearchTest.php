<?php
namespace Magento\QuickOrder\Test\Unit\Model;

use Magento\Framework\Api\Search\SearchCriteriaBuilder;

/**
 * Unit tests for Quick Order FulltextSearch model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextSearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\QuickOrder\Model\FulltextSearch
     */
    private $fulltextSearch;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Search\Api\SearchInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var string
     */
    private $query = 'test';

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->filterBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchMock = $this->getMockBuilder(\Magento\Search\Api\SearchInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactoryMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fulltextSearch = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Model\FulltextSearch::class,
            [
                'filterBuilder' => $this->filterBuilderMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'search' => $this->searchMock,
                'searchResultFactory' => $this->searchResultFactoryMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * Test for search().
     *
     * @return void
     */
    public function testSearch()
    {
        $this->prepareSearchMocks();

        $searchResultsMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchMock->expects($this->once())->method('search')->with($this->searchCriteriaMock)
            ->willReturn($searchResultsMock);
        $searchResultMock = $this->getMockBuilder(\Magento\Framework\Api\Search\DocumentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsMock->expects($this->once())->method('getItems')->willReturn([$searchResultMock]);

        $this->assertSame([$searchResultMock], $this->fulltextSearch->search($this->query, 0));
    }

    /**
     * Test for search() method when EmptyRequestDataException is thrown.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function testSearchWithEmptyRequestDataException()
    {
        $this->prepareSearchMocks();
        $exception = new \Magento\Framework\Search\Request\EmptyRequestDataException(__('Exception message'));
        $this->searchMock->expects($this->once())->method('search')->with($this->searchCriteriaMock)
            ->willThrowException($exception);
        $searchResultsMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultFactoryMock->expects($this->once())->method('create')->willReturn($searchResultsMock);
        $searchResultsMock->expects($this->once())->method('setItems')->with([])->willReturnSelf();
        $searchResultsMock->expects($this->once())->method('getItems')->willReturn([]);

        $this->assertSame([], $this->fulltextSearch->search($this->query, 0));
    }

    /**
     * Test for search() method when NonExistingRequestNameException is thrown.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage An error occurred. For details, see the error log.
     */
    public function testSearchWithNonExistingRequestNameException()
    {
        $this->prepareSearchMocks();
        $exception = new \Magento\Framework\Search\Request\NonExistingRequestNameException(__('Exception message'));
        $this->searchMock->expects($this->once())->method('search')->with($this->searchCriteriaMock)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())->method('error');

        $this->fulltextSearch->search($this->query, 0);
    }

    /**
     * Prepare mock objects for search test.
     *
     * @return void
     */
    private function prepareSearchMocks()
    {
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('search_term');
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with($this->query);
        $filterMock = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')->with($filterMock);
        $this->searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->searchCriteriaMock->expects($this->once())->method('setRequestName')->willReturnSelf();
        $this->searchCriteriaMock->expects($this->once())->method('setPageSize')->with(500)->willReturnSelf();
    }
}
