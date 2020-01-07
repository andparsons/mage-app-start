<?php
namespace Magento\Solr\SearchAdapter\Query\Builder;

/**
 * Interface \Magento\Solr\SearchAdapter\Query\Builder\QueryInterface
 *
 */
interface QueryInterface
{
    /**
     * @param \Solarium\QueryType\Select\Query\Query $select
     * @param \Magento\Framework\Search\Request\QueryInterface $query
     * @param string $conditionType
     * @return \Solarium\QueryType\Select\Query\Query
     */
    public function build(
        \Solarium\QueryType\Select\Query\Query $select,
        \Magento\Framework\Search\Request\QueryInterface $query,
        $conditionType
    );
}
