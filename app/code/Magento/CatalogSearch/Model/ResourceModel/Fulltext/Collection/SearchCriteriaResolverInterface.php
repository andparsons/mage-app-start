<?php

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Resolve specific attributes for search criteria.
 */
interface SearchCriteriaResolverInterface
{
    /**
     * Resolve specific attribute.
     *
     * @return SearchCriteria
     */
    public function resolve(): SearchCriteria;
}
