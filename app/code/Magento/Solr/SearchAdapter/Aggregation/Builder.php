<?php
namespace Magento\Solr\SearchAdapter\Aggregation;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Solr\SearchAdapter\Aggregation\Builder\BucketBuilderInterface;
use Solarium\Core\Query\Result\ResultInterface;

/**
 * @deprecated 100.2.0
 */
class Builder
{
    /**
     * @var DataProviderInterface[]
     */
    private $dataProviderContainer;

    /**
     * @var BucketBuilderInterface[]
     */
    private $aggregationContainer;

    /**
     * @param  DataProviderInterface[] $dataProviderContainer
     * @param  BucketBuilderInterface[] $aggregationContainer
     */
    public function __construct(
        array $dataProviderContainer,
        array $aggregationContainer
    ) {
        $this->dataProviderContainer = $dataProviderContainer;
        $this->aggregationContainer = $aggregationContainer;
    }

    /**
     * @param RequestInterface $request
     * @param ResultInterface $baseQueryResult
     * @return array
     */
    public function build(RequestInterface $request, ResultInterface $baseQueryResult)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();
        $dataProvider = $this->dataProviderContainer[$request->getIndex()];
        foreach ($buckets as $bucket) {
            $aggregationBuilder = $this->aggregationContainer[$bucket->getType()];
            $aggregations[$bucket->getName()] = $aggregationBuilder->build(
                $bucket,
                $request->getDimensions(),
                $baseQueryResult,
                $dataProvider
            );
        }

        return $aggregations;
    }
}
