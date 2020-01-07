<?php
namespace Magento\Solr\SearchAdapter;

use Magento\Solr\SearchAdapter\Filter\Builder;
use Magento\Solr\SearchAdapter\Query\Builder\Match as MatchQueryBuilder;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Solarium\QueryType\Select\Query\Query;

/**
 * Mapper class. Maps library request to specific adapter dependent query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapper
{
    /**
     * @var \Magento\Solr\SearchAdapter\Query\Builder\Match
     */
    private $matchQueryBuilder;

    /**
     * @var Filter\Builder
     */
    private $filterBuilder;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var Dimensions
     */
    private $dimensionsBuilder;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param QueryFactory $queryFactory
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param Builder $filterBuilder
     * @param Dimensions $dimensionsBuilder
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        QueryFactory $queryFactory,
        MatchQueryBuilder $matchQueryBuilder,
        Builder $filterBuilder,
        Dimensions $dimensionsBuilder,
        ConditionManager $conditionManager
    ) {
        $this->queryFactory = $queryFactory;
        $this->matchQueryBuilder = $matchQueryBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->dimensionsBuilder = $dimensionsBuilder;
        $this->conditionManager = $conditionManager;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @throws \Exception
     * @return Query
     */
    public function buildQuery(RequestInterface $request)
    {
        $selectQuery = $this->queryFactory->create();
        $selectQuery->setStart($request->getFrom());
        $selectQuery->setRows($request->getSize());
        $selectQuery->setFields(['id', 'score']);

        $selectQuery = $this->processQuery(
            $request->getQuery(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $this->dimensionsBuilder->build($request->getDimensions(), $selectQuery);

        return $selectQuery;
    }

    /**
     * Process query
     *
     * @param RequestQueryInterface $query
     * @param Query $selectQuery
     * @param string $conditionType
     * @return Query
     * @throws \InvalidArgumentException
     */
    protected function processQuery(
        RequestQueryInterface $query,
        Query $selectQuery,
        $conditionType
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $query */
                $selectQuery = $this->matchQueryBuilder->build(
                    $selectQuery,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $query */
                $selectQuery = $this->processBoolQuery($query, $selectQuery);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $query */
                $selectQuery = $this->processFilterQuery($query, $selectQuery, $conditionType);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $query->getType()));
        }

        return $selectQuery;
    }

    /**
     * Process bool query
     *
     * @param BoolQuery $query
     * @param Query $select
     * @return Query
     */
    private function processBoolQuery(
        BoolQuery $query,
        Query $select
    ) {
        $select = $this->processBoolQueryCondition(
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $select = $this->processBoolQueryCondition(
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD
        );

        $select = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT
        );

        return $select;
    }

    /**
     * Process bool query condition (must, should, must_not)
     *
     * @param RequestQueryInterface[] $subQueryList
     * @param Query $select
     * @param string $conditionType
     * @return Query
     */
    private function processBoolQueryCondition(
        array $subQueryList,
        Query $select,
        $conditionType
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($subQuery, $select, $conditionType);
        }

        return $select;
    }

    /**
     * Process filter query
     *
     * @param FilterQuery $query
     * @param Query $select
     * @param string $conditionType
     * @return Query
     */
    private function processFilterQuery(
        FilterQuery $query,
        Query $select,
        $conditionType
    ) {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $select = $this->processQuery($query->getReference(), $select, $conditionType);
                break;
            case FilterQuery::REFERENCE_FILTER:
                $filterQueries = $select->getFilterQueries();
                $filterQuery = [
                    'key' => sizeof($filterQueries),
                    'query' => $this->filterBuilder->build($query->getReference(), $conditionType)
                ];
                $select->addFilterQuery($filterQuery);
                break;
        }

        return $select;
    }
}
