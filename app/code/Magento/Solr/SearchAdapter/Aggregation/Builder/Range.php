<?php
namespace Magento\Solr\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Solarium\Core\Query\Result\ResultInterface;

class Range implements BucketBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        ResultInterface $baseQueryResult,
        DataProviderInterface $dataProvider
    ) {
        throw new \Exception('Not implemented yet');
    }
}
