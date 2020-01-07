<?php
namespace Magento\Solr\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

class Term implements BucketBuilderInterface
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
        $facetSet = $baseQueryResult->getFacetSet();
        $values = [];
        /** @var \Solarium\QueryType\Select\Result\Facet\Field $backet */
        foreach ($facetSet->getFacet($bucket->getName()) as $name => $count) {
            $values[$name] = [
                'value' => $name,
                'count' => $count,
            ];
        }
        return $values;
    }
}
