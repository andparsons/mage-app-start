<?php
namespace Magento\Framework\Api\Search;

/**
 * Facet Bucket
 */
interface BucketInterface
{
    /**
     * Get field name
     *
     * @return string
     */
    public function getName();

    /**
     * Get field values
     *
     * @return \Magento\Framework\Api\Search\AggregationValueInterface[]
     */
    public function getValues();
}
