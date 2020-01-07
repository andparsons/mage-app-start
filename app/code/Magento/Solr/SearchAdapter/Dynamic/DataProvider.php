<?php
namespace Magento\Solr\SearchAdapter\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;
use \Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Solr\SearchAdapter\ConnectionManager;
use Magento\Solr\SearchAdapter\Dimensions;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Magento\Solr\SearchAdapter\QueryFactory;
use Solarium\QueryType\Select\Query\Query;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var Dimensions
     */
    private $dimensionsBuilder;

    /**
     * @param ConnectionManager $connectionManager
     * @param QueryFactory $queryFactory
     * @param FieldMapperInterface $fieldMapper
     * @param Range $range
     * @param IntervalFactory $intervalFactory
     * @param Dimensions $dimensionsBuilder
     */
    public function __construct(
        ConnectionManager $connectionManager,
        QueryFactory $queryFactory,
        FieldMapperInterface $fieldMapper,
        Range $range,
        IntervalFactory $intervalFactory,
        Dimensions $dimensionsBuilder
    ) {
        $this->connectionManager = $connectionManager;
        $this->queryFactory = $queryFactory;
        $this->fieldMapper = $fieldMapper;
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->dimensionsBuilder = $dimensionsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];

        $mergedEntityIds = implode(' ' . Query::QUERY_OPERATOR_OR . ' ', $entityStorage->getSource());

        $query = $this->queryFactory->create();

        $fieldName = $this->fieldMapper->getFieldName('price');
        $query->setFields([$fieldName]);

        $stats = $query->getStats();
        $stats->createField($fieldName);

        $query->createFilterQuery('aggregations')
            ->setQuery('id:(%1%)', [$mergedEntityIds]);

        $resultSet = $this->connectionManager->getConnection()
            ->query($query);

        /** @var \Solarium\QueryType\Select\Result\Stats\Stats $statsResult */
        $statsResult = $resultSet->getStats();
        $statsField = $statsResult->getResult($fieldName);

        if ($statsField !== null) {
            $aggregations = [
                'count' => $statsField->getCount(),
                'max' => $statsField->getMax(),
                'min' => $statsField->getMin(),
                'std' => $statsField->getStddev(),
            ];
        }

        return $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $mergedEntityIds = implode(' ' . Query::QUERY_OPERATOR_OR . ' ', $entityStorage->getSource());

        $query = $this->queryFactory->create();

        $this->dimensionsBuilder->build($dimensions, $query);

        $fieldName = $this->fieldMapper->getFieldName($bucket->getField());
        $query->addField($fieldName)
            ->createFilterQuery('interval')
            ->setQuery('id:(%1%)', [$mergedEntityIds]);

        return $this->intervalFactory->create(['query' => $query, 'fieldName' => $fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $result = [];

        if (!$entityStorage->getSource()) {
            return $result;
        }

        $query = $this->queryFactory->create();
        $query->createFilterQuery('ids')
            ->setQuery(
                'id:(%1%)',
                [
                    implode(' ' . Query::QUERY_OPERATOR_OR . ' ', $entityStorage->getSource()),
                ]
            );
        $this->dimensionsBuilder->build($dimensions, $query);

        $facetSet = $query->getFacetSet();
        /** @var \Solarium\QueryType\Select\Query\Component\Facet\Range $facet */
        $facet = $facetSet->createFacetRange($bucket->getName());
        $facet->setField($this->fieldMapper->getFieldName($bucket->getField()));
        $facet->setStart(0);
        $facet->setEnd($this->getAggregations($entityStorage)['max']);
        $facet->setGap($range);
        $facet->setMinCount(1);

        $resultBucket = $this->connectionManager->getConnection()
            ->query($query)
            ->getFacetSet()
            ->getFacet($bucket->getName());
        foreach ($resultBucket as $rangeStart => $count) {
            $result[$rangeStart / $range + 1] = $count;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];

            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;

                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }
}
