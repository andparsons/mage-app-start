<?php
namespace Magento\Solr\Test\Unit\SearchAdapter\Dynamic;

use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Solr\SearchAdapter\ConnectionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Solarium\QueryType\Select\Query\Component\Stats\Stats;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param int[] $entityIds
     * @param string $mergedEntityIds
     * @param bool $isStatsField
     * @param array $expectedResult
     * @dataProvider getAggregationsProvider
     */
    public function testGetAggregations($entityIds, $mergedEntityIds, $isStatsField, $expectedResult)
    {
        $filterQuery = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
            ->setMethods(['setQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery->expects($this->once())->method('setQuery')->withConsecutive(['id:(%1%)', [$mergedEntityIds]]);

        $fieldName = 'price_0_1';

        $fieldMapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\FieldMapperInterface::class)
            ->setMethods(['getFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMapper->expects($this->once())
            ->method('getFieldName')
            ->withConsecutive(['price'])
            ->willReturn($fieldName);

        /** @var Stats|\PHPUnit_Framework_MockObject_MockObject $componentStats */
        $componentStats = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Stats\Stats::class)
            ->setMethods(['createField'])
            ->disableOriginalConstructor()
            ->getMock();
        $componentStats->expects($this->once())->method('createField')->withConsecutive([$fieldName]);

        /** @var \Solarium\QueryType\Select\Query\Query|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->setMethods(['createFilterQuery', 'getStats'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('createFilterQuery')
            ->withConsecutive(['aggregations'])
            ->willReturn($filterQuery);
        $query->expects($this->once())->method('getStats')->willReturn($componentStats);

        /** @var \Magento\Solr\SearchAdapter\QueryFactory|\PHPUnit_Framework_MockObject_MockObject $queryFactory */
        $queryFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\QueryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryFactory->expects($this->once())->method('create')->willReturn($query);

        /** @var \Solarium\QueryType\Select\Result\Stats\Result|\PHPUnit_Framework_MockObject_MockObject $statsField */
        $statsField = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Result::class)
            ->setMethods(['getCount', 'getMax', 'getMin', 'getStddev'])
            ->disableOriginalConstructor()
            ->getMock();
        $statsField->expects($this->any())->method('getCount')->willReturn($expectedResult['count']);
        $statsField->expects($this->any())->method('getMax')->willReturn($expectedResult['max']);
        $statsField->expects($this->any())->method('getMin')->willReturn($expectedResult['min']);
        $statsField->expects($this->any())->method('getStddev')->willReturn($expectedResult['std']);

        /** @var \Solarium\QueryType\Select\Result\Stats\Stats|\PHPUnit_Framework_MockObject_MockObject $resultStats */
        $resultStats = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Stats::class)
            ->setMethods(['getResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultStats->expects($this->once())
            ->method('getResult')
            ->withConsecutive([$fieldName])
            ->willReturn($isStatsField ? $statsField : null);

        /** @var \Solarium\Core\Query\Result\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultSet */
        $resultSet = $this->getMockBuilder(\Solarium\Core\Query\Result\ResultInterface::class)
            ->setMethods(['getStats'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultSet->expects($this->once())->method('getStats')->willReturn($resultStats);

        /** @var \Magento\Solr\Model\Client\Solarium|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())->method('query')->withConsecutive([$query])->willReturn($resultSet);

        /** @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject $connectionManager */
        $connectionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $connectionManager->expects($this->once())->method('getConnection')->willReturn($client);

        /** @var EntityStorage|\PHPUnit_Framework_MockObject_MockObject $entityStorage */
        $entityStorage = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\EntityStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn($entityIds);

        /** @var \Magento\Solr\SearchAdapter\Dynamic\DataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject(
            \Magento\Solr\SearchAdapter\Dynamic\DataProvider::class,
            [
                'queryFactory' => $queryFactory,
                'fieldMapper' => $fieldMapper,
                'connectionManager' => $connectionManager
            ]
        );

        $this->assertEquals($expectedResult, $dataProvider->getAggregations($entityStorage));
    }

    /**
     * Data provider for method testGetInterval
     *
     * @return array
     */
    public function getAggregationsProvider()
    {
        return [
            [[1], '1', true, ['count' => 23, 'max' => 342, 'min' => 123, 'std' => 235.245124]],
            [[1, 2], '1 OR 2', true, ['count' => 26, 'max' => 312, 'min' => 163, 'std' => 225.245124]],
            [[1, 2, 3], '1 OR 2 OR 3', true, ['count' => 14, 'max' => 352, 'min' => 231, 'std' => 235.45672]],
            [[], '', true, ['count' => 35, 'max' => 845, 'min' => 642, 'std' => 735.245124]],
            [[5, 3, 2], '5 OR 3 OR 2', false, ['count' => 0, 'max' => 0, 'min' => 0, 'std' => 0]]
        ];
    }

    /**
     * @param int[] $entityIds
     * @param string $mergedEntityIds
     * @dataProvider getIntervalProvider
     */
    public function testGetInterval($entityIds, $mergedEntityIds)
    {
        $fieldName = 'fieldName';
        $filterQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\FilterQuery::class)
            ->setMethods(['setQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery->expects($this->once())
            ->method('setQuery')
            ->withConsecutive(['id:(%1%)', [$mergedEntityIds]]);

        $query = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('addField')
            ->withConsecutive([$fieldName])
            ->willReturnSelf();
        $query->expects($this->once())
            ->method('createFilterQuery')
            ->withConsecutive(['interval'])
            ->willReturn($filterQuery);

        /** @var \Magento\Solr\SearchAdapter\QueryFactory|\PHPUnit_Framework_MockObject_MockObject $queryFactory */
        $queryFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\QueryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryFactory->expects($this->once())
            ->method('create')
            ->willReturn($query);

        /** @var IntervalFactory|\PHPUnit_Framework_MockObject_MockObject $intervalFactory */
        $intervalFactory = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\IntervalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $intervalFactory->expects($this->any())
            ->method('create')
            ->withConsecutive([['query' => $query, 'fieldName' => $fieldName]])
            ->willReturn('expectedResult');

        $fieldMapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->withConsecutive([$fieldName])
            ->willReturnArgument(0);

        /** @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject $bucket */
        $bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $bucket->expects($this->any())
            ->method('getField')
            ->willReturn($fieldName);

        /** @var EntityStorage|\PHPUnit_Framework_MockObject_MockObject $entityStorage */
        $entityStorage = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\EntityStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn($entityIds);

        /** @var \Magento\Solr\SearchAdapter\Dynamic\DataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject(
            \Magento\Solr\SearchAdapter\Dynamic\DataProvider::class,
            ['intervalFactory' => $intervalFactory, 'queryFactory' => $queryFactory, 'fieldMapper' => $fieldMapper]
        );

        $result = $dataProvider->getInterval($bucket, [], $entityStorage);

        $this->assertEquals('expectedResult', $result);
    }

    /**
     * Data provider for method testGetInterval
     *
     * @return array
     */
    public function getIntervalProvider()
    {
        return [
            [[1], '1'],
            [[1, 2], '1 OR 2'],
            [[1, 2, 3], '1 OR 2 OR 3'],
            [[], '']
        ];
    }

    public function testGetRange()
    {
        $expectedResult = 10;

        $range = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Filter\Price\Range::class)
            ->setMethods(['getPriceRange'])
            ->disableOriginalConstructor()
            ->getMock();

        $range->expects($this->once())
            ->method('getPriceRange')
            ->willReturn($expectedResult);

        /** @var \Magento\Solr\SearchAdapter\Dynamic\DataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject(
            \Magento\Solr\SearchAdapter\Dynamic\DataProvider::class,
            ['range' => $range]
        );

        $result = $dataProvider->getRange();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param int $range
     * @param array $dbRanges
     * @param array $exceptedResult
     * @dataProvider prepareDataProvider
     */
    public function testPrepareData($range, $dbRanges, $exceptedResult)
    {
        /** @var \Magento\Solr\SearchAdapter\Dynamic\DataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject(\Magento\Solr\SearchAdapter\Dynamic\DataProvider::class);

        $result = $dataProvider->prepareData($range, $dbRanges);

        $this->assertEquals($exceptedResult, $result);
    }

    /**
     * Data provider for method testPrepareData
     *
     * @return array
     */
    public function prepareDataProvider()
    {
        return [
            [3, [], []],
            [
                10,
                [1 => 5, 2 => 2, 3 => 4],
                [
                    ['from' => '', 'to' => 10, 'count' => 5],
                    ['from' => 10, 'to' => 20, 'count' => 2],
                    ['from' => 20, 'to' => '', 'count' => 4]
                ]
            ]
        ];
    }

    public function testGetAggregation()
    {
        $entityIds = [1,2];
        $dimensions = ['store' => 'default'];
        $range = 1000;
        $bucketName = 'price';

        $filterQuery = $this->getMockBuilder(\Solarium\QueryType\Select\Query\FilterQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterQuery->expects($this->once())
            ->method('setQuery')
            ->withConsecutive(['id:(%1%)', ['1 OR 2']]);

        $facetSet = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\FacetSet::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('createFilterQuery')
            ->with('ids')
            ->willReturn($filterQuery);

        $query->expects($this->once())
            ->method('getFacetSet')
            ->willReturn($facetSet);

        $facet = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Facet\Range::class)
            ->disableOriginalConstructor()
            ->getMock();

        $facetSet->expects($this->once())
            ->method('createFacetRange')
            ->with($bucketName)
            ->willReturn($facet);

        $dimensionsBuilder = $this->getMockBuilder(\Magento\Solr\SearchAdapter\Dimensions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dimensionsBuilder->expects($this->once())
            ->method('build')
            ->with($dimensions, $query);

        $facet->expects($this->any())->method('setField');
        $facet->expects($this->any())->method('setStart')->with(0);
        $facet->expects($this->any())->method('setEnd')->with(342);
        $facet->expects($this->any())->method('setGap')->with($range);
        $facet->expects($this->any())->method('setMinCount')->with(1);

        /** @var \Magento\Solr\SearchAdapter\QueryFactory|\PHPUnit_Framework_MockObject_MockObject $queryFactory */
        $queryFactory = $this->getMockBuilder(\Magento\Solr\SearchAdapter\QueryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($query);

        /** @var ConnectionManager|\PHPUnit_Framework_MockObject_MockObject $connectionManager */
        $connectionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConnectionManager::class)
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Search\Request\BucketInterface|\PHPUnit_Framework_MockObject_MockObject $bucket */
        $bucket = $this->getMockBuilder(\Magento\Framework\Search\Request\BucketInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $bucket->expects($this->any())
            ->method('getName')->willReturn('price');

        $this->getAggregationsMock($connectionManager, $queryFactory);

        /** @var EntityStorage|\PHPUnit_Framework_MockObject_MockObject $entityStorage */
        $entityStorage = $this->getMockBuilder(\Magento\Framework\Search\Dynamic\EntityStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityStorage->expects($this->any())
            ->method('getSource')
            ->willReturn($entityIds);

        /** @var \Magento\Solr\SearchAdapter\Dynamic\DataProvider $dataProvider */
        $dataProvider = $this->objectManager->getObject(
            \Magento\Solr\SearchAdapter\Dynamic\DataProvider::class,
            [
                'queryFactory' => $queryFactory,
                'connectionManager' => $this->getConnectionManager($connectionManager, $query),
                'dimensionsBuilder' => $dimensionsBuilder
            ]
        );

        $this->assertEquals(
            [1 => 3],
            $dataProvider->getAggregation($bucket, $dimensions, $range, $entityStorage)
        );
    }

    /**
     * @return ConnectionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConnectionManager($connectionManager, $query)
    {

        $facetSets = $this->getMockBuilder(\Solarium\QueryType\Select\Result\FacetSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $facetSets->expects($this->any())->method('getFacet')->with('price')->willReturn([0 => 2, 1 => 3]);

        /** @var \Solarium\Core\Query\Result\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultSet */
        $resultSet = $this->getMockBuilder(\Solarium\Core\Query\Result\ResultInterface::class)
            ->setMethods(['getFacetSet'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultSet->expects($this->once())->method('getFacetSet')->willReturn($facetSets);

        /** @var \Magento\Solr\Model\Client\Solarium|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('query')
            ->withConsecutive([$query])
            ->willReturn($resultSet);

        $connectionManager->expects($this->at(1))
            ->method('getConnection')
            ->willReturn($client);

        return $connectionManager;
    }

    public function getAggregationsMock($connectionManager, $queryFactory)
    {
        $expectedResult = ['count' => 23, 'max' => 342, 'min' => 123, 'std' => 235.245124];
        $filterQuery = $this->getMockBuilder(\Magento\Framework\Search\Request\Query\Filter::class)
            ->setMethods(['setQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterQuery->expects($this->once())->method('setQuery')->withConsecutive(['id:(%1%)', ['1 OR 2']]);

        $fieldName = 'price_0_1';

        $fieldMapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\FieldMapperInterface::class)
            ->setMethods(['getFieldName'])
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->withConsecutive(['price'])
            ->willReturn($fieldName);

        /** @var Stats|\PHPUnit_Framework_MockObject_MockObject $componentStats */
        $componentStats = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Stats\Stats::class)
            ->setMethods(['createField'])
            ->disableOriginalConstructor()
            ->getMock();
        $componentStats->expects($this->once())->method('createField');

        /** @var \Solarium\QueryType\Select\Query\Query|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->setMethods(['createFilterQuery', 'getStats'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())
            ->method('createFilterQuery')
            ->withConsecutive(['aggregations'])
            ->willReturn($filterQuery);
        $query->expects($this->once())->method('getStats')->willReturn($componentStats);

        /** @var \Magento\Solr\SearchAdapter\QueryFactory|\PHPUnit_Framework_MockObject_MockObject $queryFactory */
        $queryFactory->expects($this->at(1))->method('create')->willReturn($query);

        /** @var \Solarium\QueryType\Select\Result\Stats\Result|\PHPUnit_Framework_MockObject_MockObject $statsField */
        $statsField = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Result::class)
            ->setMethods(['getCount', 'getMax', 'getMin', 'getStddev'])
            ->disableOriginalConstructor()
            ->getMock();
        $statsField->expects($this->any())->method('getCount')->willReturn($expectedResult['count']);
        $statsField->expects($this->any())->method('getMax')->willReturn($expectedResult['max']);
        $statsField->expects($this->any())->method('getMin')->willReturn($expectedResult['min']);
        $statsField->expects($this->any())->method('getStddev')->willReturn($expectedResult['std']);

        /** @var \Solarium\QueryType\Select\Result\Stats\Stats|\PHPUnit_Framework_MockObject_MockObject $resultStats */
        $resultStats = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Stats\Stats::class)
            ->setMethods(['getResult'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultStats->expects($this->once())
            ->method('getResult')
            ->willReturn($statsField);

        /** @var \Solarium\Core\Query\Result\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultSet */
        $resultSet = $this->getMockBuilder(\Solarium\Core\Query\Result\ResultInterface::class)
            ->setMethods(['getStats'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultSet->expects($this->once())->method('getStats')->willReturn($resultStats);

        /** @var \Magento\Solr\Model\Client\Solarium|\PHPUnit_Framework_MockObject_MockObject $client */
        $client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())->method('query')->withConsecutive([$query])->willReturn($resultSet);

        $connectionManager->expects($this->at(0))->method('getConnection')->willReturn($client);
    }
}
