<?php
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Elasticsearch\SearchAdapter\Query\Builder as QueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder as FilterBuilder;
use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper as Elasticsearch5Mapper;

/**
 * Mapper class
 * @api
 * @since 100.1.0
 */
class Mapper extends Elasticsearch5Mapper
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        MatchQueryBuilder $matchQueryBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return array
     * @since 100.1.0
     */
    public function buildQuery(RequestInterface $request)
    {
        $searchQuery = $this->queryBuilder->initQuery($request);
        $searchQuery['body']['query'] = array_merge(
            $searchQuery['body']['query'],
            $this->processQuery(
                $request->getQuery(),
                [],
                BoolQuery::QUERY_CONDITION_MUST
            )
        );

        $searchQuery['body']['query']['bool']['minimum_should_match'] = 1;

        $searchQuery = $this->queryBuilder->initAggregations($request, $searchQuery);
        return $searchQuery;
    }
}
