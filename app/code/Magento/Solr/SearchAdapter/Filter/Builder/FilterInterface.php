<?php
namespace Magento\Solr\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;

/**
 * Interface \Magento\Solr\SearchAdapter\Filter\Builder\FilterInterface
 *
 */
interface FilterInterface
{
    const NEGATION_OPERATOR = '-';

    /**
     * @param RequestFilterInterface $filter
     * @return string
     */
    public function buildFilter(RequestFilterInterface $filter);
}
