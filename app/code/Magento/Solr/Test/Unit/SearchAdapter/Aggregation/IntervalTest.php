<?php
namespace Magento\Solr\Test\Unit\SearchAdapter\Aggregation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Solr\SearchAdapter\Aggregation\Interval;
use Solarium\QueryType\Select\Query\Query;

class IntervalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Interval
     */
    private $interval;

    /**
     * @var \Solarium\QueryType\Select\Query\Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    /**
     * @var \Magento\Solr\Model\Client\Solarium|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var string
     */
    private $fieldName = 'some_field';

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->client);

        $this->query = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->interval = $helper->getObject(
            \Magento\Solr\SearchAdapter\Aggregation\Interval::class,
            ['connectionManager' => $connectionManager, 'query' => $this->query, 'fieldName' => $this->fieldName]
        );
    }

    public function testLoad($at = 0)
    {
        $filterQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\FilterQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->query->expects($this->any())
            ->method('createFilterQuery')
            ->willReturn($filterQuery);
        $this->query->expects($this->any())
            ->method('addSort')
            ->withConsecutive([$this->fieldName, Query::SORT_ASC])
            ->willReturnSelf();
        $this->query->expects($this->any())
            ->method('setStart')
            ->withConsecutive([20])
            ->willReturnSelf();
        $this->query->expects($this->any())
            ->method('setRows')
            ->withConsecutive([10]);

        $this->client->expects($this->at($at))
            ->method('query')
            ->withConsecutive([$this->query])
            ->willReturn([[$this->fieldName => 123.23]]);

        $result = $this->interval->load(10, 20, 5, 50);

        $this->assertEquals([123.23], $result);
    }

    /**
     * @param int $count
     * @param bool|float[] $expectedResult
     * @dataProvider loadPreviousProvider
     */
    public function testLoadPrevious($count, $expectedResult)
    {
        $filterQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\FilterQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stats = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Stats\Stats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statsField = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statsField->expects($this->any())
            ->method('getCount')
            ->willReturn($count);

        $statsResult = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Stats::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statsResult->expects($this->any())
            ->method('getResult')
            ->willReturn($statsField);

        $resultSet = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultSet->expects($this->any())
            ->method('getStats')
            ->willReturn($statsResult);

        $this->query->expects($this->any())
            ->method('createFilterQuery')
            ->willReturn($filterQuery);
        $this->query->expects($this->any())
            ->method('getStats')
            ->willReturn($stats);
        $this->query->expects($this->any())
            ->method('addSort')
            ->willReturnSelf();
        $this->query->expects($this->any())
            ->method('setStart')
            ->willReturnSelf();

        $this->client->expects($this->any())
            ->method('query')
            ->withConsecutive([$this->query])
            ->willReturn($resultSet);

        $resultSet->expects($this->any())
            ->method('getIterator')
            ->willReturn(
                new \ArrayIterator([[$this->fieldName => 123.23]])
            );

        $result = $this->interval->loadPrevious(123.12, 25, 50);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param $count
     * @param bool|float[] $expectedResult
     * @dataProvider loadNextProvider
     */
    public function testLoadNext($count, $expectedResult)
    {
        $filterQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\FilterQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stats = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Stats\Stats::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statsField = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statsField->expects($this->any())
            ->method('getCount')
            ->willReturn($count);

        $statsResult = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Stats::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statsResult->expects($this->any())
            ->method('getResult')
            ->willReturn($statsField);

        $resultSet = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Result::class)
            ->setMethods(['getStats', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultSet->expects($this->any())
            ->method('getStats')
            ->willReturn($statsResult);
        $resultSet->expects($this->any())
            ->method('getIterator')
            ->willReturn(
                new \ArrayIterator([[$this->fieldName => 123.23], [$this->fieldName => 234.45]])
            );

        $this->query->expects($this->any())
            ->method('createFilterQuery')
            ->willReturn($filterQuery);
        $this->query->expects($this->any())
            ->method('getStats')
            ->willReturn($stats);
        $this->query->expects($this->any())
            ->method('addSort')
            ->withConsecutive([$this->fieldName, Query::SORT_ASC])
            ->willReturnSelf();
        $this->query->expects($this->any())
            ->method('setStart')
            ->withConsecutive([9])
            ->willReturnSelf();
        $this->query->expects($this->any())
            ->method('setRows')
            ->withConsecutive([10]);

        $this->client->expects($this->any())
            ->method('query')
            ->withConsecutive([$this->query])
            ->willReturn($resultSet);

        $result = $this->interval->loadNext(125.15, 19, 500);

        $this->assertEquals($expectedResult, $result);
    }

    public function loadNextProvider()
    {
        return [[0, false], [10, [234.45, 123.23]]];
    }

    public function loadPreviousProvider()
    {
        return [[0, false], [10, [123.23]]];
    }
}
