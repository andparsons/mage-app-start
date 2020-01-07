<?php
namespace Magento\Solr\SearchAdapter\Aggregation;

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Solarium\QueryType\Select\Query\Query;

class StatisticsBuilder
{
    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(
        FieldMapperInterface $fieldMapper
    ) {
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * Build statistics for aggregation
     *
     * @param RequestInterface $request
     * @param Query $query
     * @return Query
     */
    public function build(RequestInterface $request, Query $query)
    {
        $buckets = $request->getAggregation();
        foreach ($buckets as $bucket) {
            $this->buildBucket($query, $bucket);
        }
        return $query;
    }

    /**
     * Build statistics for bucket
     *
     * @param Query $query
     * @param BucketInterface $bucket
     * @return void
     */
    private function buildBucket(Query $query, BucketInterface $bucket)
    {
        switch ($bucket->getType()) {
            case BucketInterface::TYPE_TERM:
                $facetSet = $query->getFacetSet();
                $facet = $facetSet->createFacetField($bucket->getName());
                $facet->setField($this->fieldMapper->getFieldName($bucket->getField()));
                $facet->setMinCount(1);
                break;

            case BucketInterface::TYPE_DYNAMIC:
                $stats = $query->getStats();
                $stats->createField($this->fieldMapper->getFieldName($bucket->getField()));
                break;
        }
    }
}
