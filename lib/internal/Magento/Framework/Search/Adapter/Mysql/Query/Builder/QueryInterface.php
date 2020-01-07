<?php
namespace Magento\Framework\Search\Adapter\Mysql\Query\Builder;

use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;

/**
 * MySQL search query builder.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
interface QueryInterface
{
    /**
     * Build query.
     *
     * @param \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder $scoreBuilder
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Framework\Search\Request\QueryInterface $query
     * @param string $conditionType
     * @return \Magento\Framework\DB\Select
     */
    public function build(
        ScoreBuilder $scoreBuilder,
        \Magento\Framework\DB\Select $select,
        \Magento\Framework\Search\Request\QueryInterface $query,
        $conditionType
    );
}
