<?php
namespace Magento\Solr\Test\Unit\SearchAdapter;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Solr\SearchAdapter\Mapper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MapperTest extends \PHPUnit\Framework\TestCase
{
    const INDEX_NAME = 'test_index_fulltext';

    /**
     * @var \Magento\Solr\SearchAdapter\QueryFactory|MockObject
     */
    private $queryFactory;

    /**
     * @var \Magento\Solr\SearchAdapter\Query\Builder\Match|MockObject
     */
    private $matchQueryBuilder;

    /**
     * @var \Magento\Solr\SearchAdapter\Filter\Builder|MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Search\RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Mapper
     */
    private $mapper;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->queryFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\QueryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->matchQueryBuilder = $this->getMockBuilder(\Magento\Solr\SearchAdapter\Query\Builder\Match::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilder = $this->getMockBuilder(\Magento\Solr\SearchAdapter\Filter\Builder::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\Search\RequestInterface::class)
            ->setMethods(['getQuery', 'getDimensions', 'getIndex', 'getFrom', 'getSize'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->mapper = $helper->getObject(
            \Magento\Solr\SearchAdapter\Mapper::class,
            [
                'queryFactory' => $this->queryFactory,
                'matchQueryBuilder' => $this->matchQueryBuilder,
                'filterBuilder' => $this->filterBuilder
            ]
        );
    }

    public function testBuildMatchQuery()
    {
        $query = $this->createMatchQuery();
        $selectQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->setMethods(['setStart', 'setRows'])
            ->getMock();

        $this->queryFactory->expects($this->once())->method('create')->willReturn($selectQuery);
        $this->request->expects($this->once())->method('getFrom')->willReturn(1);
        $this->request->expects($this->once())->method('getSize')->willReturn(10000);
        $selectQuery->expects($this->once())->method('setStart')->willReturnSelf();
        $selectQuery->expects($this->once())->method('setRows')->willReturnSelf();
        $this->request->expects($this->once())->method('getQuery')->willReturn($query);
        $this->request->expects($this->any())->method('getDimensions')->willReturn([]);
        $this->matchQueryBuilder->expects($this->once())->method('build')
            ->with(
                $this->equalTo($selectQuery),
                $this->equalTo($query),
                $this->equalTo(BoolQuery::QUERY_CONDITION_MUST)
            )
            ->will($this->returnValue($selectQuery));

        $response = $this->mapper->buildQuery($this->request);
        $this->assertEquals($selectQuery, $response);
    }

    public function testBuildBoolQuery()
    {
        $boolQuery = $this->createBoolQuery();
        $subQuery = $this->createMatchQuery();
        $selectQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFactory->expects($this->once())->method('create')->willReturn($selectQuery);
        $this->request->expects($this->once())->method('getFrom')->willReturn(1);
        $this->request->expects($this->once())->method('getSize')->willReturn(10000);
        $selectQuery->expects($this->once())->method('setStart')->willReturnSelf();
        $selectQuery->expects($this->once())->method('setRows')->willReturnSelf();
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($boolQuery));
        $this->request->expects($this->any())->method('getDimensions')->willReturn([]);
        $boolQuery->expects($this->once())->method('getMust')->willReturn([$subQuery]);
        $this->matchQueryBuilder->expects($this->once())->method('build')->willReturn($selectQuery);
        $boolQuery->expects($this->once())->method('getShould')->willReturn([]);
        $boolQuery->expects($this->once())->method('getMustNot')->willReturn([]);

        $response = $this->mapper->buildQuery($this->request);
        $this->assertEquals($selectQuery, $response);
    }

    public function testBuildFilterQueryWithReferenceTypeFilter()
    {
        $filterQuery = $this->createFilterQuery();
        $selectQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $referenceFilter = $this->getMockBuilder(\Magento\Framework\Search\Request\FilterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFactory->expects($this->once())->method('create')->willReturn($selectQuery);
        $this->request->expects($this->once())->method('getFrom')->willReturn(1);
        $this->request->expects($this->once())->method('getSize')->willReturn(10000);
        $selectQuery->expects($this->once())->method('setStart')->willReturnSelf();
        $selectQuery->expects($this->once())->method('setRows')->willReturnSelf();
        $selectQuery->expects($this->once())->method('getFilterQueries')->willReturn(['some_array_of_filter_queries']);
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($filterQuery));
        $this->request->expects($this->any())->method('getDimensions')->willReturn([]);
        $filterQuery->expects($this->once())->method('getReferenceType')->willReturn(FilterQuery::REFERENCE_FILTER);
        $filterQuery->expects($this->once())->method('getReference')->willReturn($referenceFilter);
        $this->filterBuilder->expects($this->once())
            ->method('build')
            ->with($referenceFilter, BoolQuery::QUERY_CONDITION_MUST)
            ->willReturn($filter);

        $response = $this->mapper->buildQuery($this->request);
        $this->assertEquals($selectQuery, $response);
    }

    public function testBuildFilterQueryWithReferenceTypeQuery()
    {
        $filterQuery = $this->createFilterQuery();
        $selectQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $referenceQuery = $this->createMatchQuery();

        $this->queryFactory->expects($this->once())->method('create')->willReturn($selectQuery);
        $this->request->expects($this->once())->method('getFrom')->willReturn(1);
        $this->request->expects($this->once())->method('getSize')->willReturn(10000);
        $selectQuery->expects($this->once())->method('setStart')->willReturnSelf();
        $selectQuery->expects($this->once())->method('setRows')->willReturnSelf();
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($filterQuery));
        $this->request->expects($this->any())->method('getDimensions')->willReturn([]);
        $filterQuery->expects($this->once())->method('getReferenceType')->willReturn(FilterQuery::REFERENCE_QUERY);
        $filterQuery->expects($this->once())->method('getReference')->willReturn($referenceQuery);
        $this->matchQueryBuilder->expects($this->once())->method('build')->willReturn($selectQuery);

        $response = $this->mapper->buildQuery($this->request);
        $this->assertEquals($selectQuery, $response);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown query type 'wrong'
     */
    public function testWrongQueryType()
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryFactory->expects($this->once())->method('create')->willReturn($selectQuery);
        $this->request->expects($this->once())->method('getFrom')->willReturn(1);
        $this->request->expects($this->once())->method('getSize')->willReturn(10000);
        $selectQuery->expects($this->once())->method('setStart')->willReturnSelf();
        $selectQuery->expects($this->once())->method('setRows')->willReturnSelf();
        $this->request->expects($this->once())->method('getQuery')->will($this->returnValue($query));
        $query->expects($this->any())->method('getType')
            ->will($this->returnValue('wrong'));

        $this->mapper->buildQuery($this->request);
    }

    private function createMatchQuery()
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Match::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_MATCH));

        return $query;
    }

    private function createFilterQuery()
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_FILTER));

        return $query;
    }

    private function createBoolQuery()
    {
        $query = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\BoolExpression::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(QueryInterface::TYPE_BOOL));

        return $query;
    }
}
