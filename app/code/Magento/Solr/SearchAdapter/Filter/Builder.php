<?php
namespace Magento\Solr\SearchAdapter\Filter;

use Magento\Solr\SearchAdapter\ConditionManager;
use Magento\Solr\SearchAdapter\Filter\Builder\FilterInterface;
use Magento\Solr\SearchAdapter\Filter\Builder\Range;
use Magento\Solr\SearchAdapter\Filter\Builder\Term;
use Magento\Solr\SearchAdapter\Filter\Builder\Wildcard;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Solarium\QueryType\Select\Query\Query;

class Builder implements BuilderInterface
{
    const OPERATOR_NEGATION = '-';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @param Range $range
     * @param Term $term
     * @param Wildcard $wildcard
     * @param ConditionManager $conditionManager
     */
    public function __construct(
        Range $range,
        Term $term,
        Wildcard $wildcard,
        ConditionManager $conditionManager
    ) {
        $this->filters = [
            RequestFilterInterface::TYPE_RANGE => $range,
            RequestFilterInterface::TYPE_TERM => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard,
        ];
        $this->conditionManager = $conditionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestFilterInterface $filter, $conditionType)
    {
        return $this->processFilter($filter, $this->isNegation($conditionType));
    }

    /**
     * @param RequestFilterInterface $filter
     * @param bool $isNegation
     * @return string
     */
    private function processFilter(RequestFilterInterface $filter, $isNegation)
    {
        if (RequestFilterInterface::TYPE_BOOL == $filter->getType()) {
            $query = $this->processBoolFilter($filter, $isNegation);
            $query = $this->conditionManager->wrapBrackets($query);
        } else {
            if (!array_key_exists($filter->getType(), $this->filters)) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }
            $query = $this->filters[$filter->getType()]->buildFilter($filter);
            if ($isNegation) {
                $query = $this->conditionManager->addNegation($query);
            }
        }

        return $this->conditionManager->wrapBrackets($query);
    }

    /**
     * @param RequestFilterInterface|\Magento\Framework\Search\Request\Filter\BoolExpression $filter
     * @param bool $isNegation
     * @return string
     */
    private function processBoolFilter(RequestFilterInterface $filter, $isNegation)
    {
        $must = $this->buildFilters($filter->getMust(), Query::QUERY_OPERATOR_AND, $isNegation);
        $should = $this->buildFilters($filter->getShould(), Query::QUERY_OPERATOR_OR, $isNegation);
        $mustNot = $this->buildFilters($filter->getMustNot(), Query::QUERY_OPERATOR_AND, !$isNegation);

        $queries = [
            $must,
            $this->conditionManager->wrapBrackets($should),
            $this->conditionManager->wrapBrackets($mustNot),
        ];

        return $this->conditionManager->combineQueries($queries, Query::QUERY_OPERATOR_AND);
    }

    /**
     * @param \Magento\Framework\Search\Request\FilterInterface[] $filters
     * @param string $unionOperator
     * @param bool $isNegation
     * @return string
     */
    private function buildFilters(array $filters, $unionOperator, $isNegation)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $queries[] = $this->processFilter($filter, $isNegation);
        }
        return $this->conditionManager->combineQueries($queries, $unionOperator);
    }

    /**
     * @param string $conditionType
     * @return bool
     */
    private function isNegation($conditionType)
    {
        return BoolExpression::QUERY_CONDITION_NOT === $conditionType;
    }
}
