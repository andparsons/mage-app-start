<?php
namespace Magento\Solr\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Solr\SearchAdapter\ConditionManager;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Solarium\QueryType\Select\Query\Query;

class Match implements QueryInterface
{
    const MINIMAL_CHARACTER_LENGTH = 3;

    const CONDITION_PATTERN = '%s:%s';

    const DEFAULT_CONDITION = '*:*';

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var FieldMapperInterface
     */
    private $mapper;

    /**
     * @param ConditionManager $conditionManager
     * @param FieldMapperInterface $mapper
     */
    public function __construct(
        ConditionManager $conditionManager,
        FieldMapperInterface $mapper
    ) {
        $this->conditionManager = $conditionManager;
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        Query $select,
        RequestQueryInterface $query,
        $conditionType
    ) {
        /** @var $query \Magento\Framework\Search\Request\Query\Match */
        $queryValue = $this->prepareQuery($query->getValue(), $conditionType);

        $conditions[] = ($select->getQuery() == self::DEFAULT_CONDITION) ? '' : $select->getQuery();
        $conditions[] = $this->buildQuery($query->getMatches(), $queryValue);
        $condition = $this->conditionManager->combineQueries($conditions, Query::QUERY_OPERATOR_AND);

        $select->setQuery($condition);

        return $select;
    }

    /**
     * @param string $field
     * @param string $value
     * @return string
     */
    private function buildCondition($field, $value)
    {
        return sprintf(self::CONDITION_PATTERN, $field, $value);
    }

    /**
     * @param array $matches
     * @param string $queryString
     * @return string
     */
    private function buildQuery($matches, $queryString)
    {
        $conditions = [];
        foreach ($matches as $match) {
            $resolvedField = $this->mapper->getFieldName($match['field'], ['type' => FieldMapperInterface::TYPE_QUERY]);
            $conditions[] = $this->buildCondition($resolvedField, $queryString);
        }
        return $this->conditionManager->combineQueries($conditions, Query::QUERY_OPERATOR_OR);
    }

    /**
     * @param string $queryValue
     * @param string $conditionType
     * @return string
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $queryValue = $this->_escape($queryValue);
        $queryValues = explode(' ', $queryValue);

        $prefix = $conditionType === BoolExpression::QUERY_CONDITION_NOT ? '-' : '';

        foreach ($queryValues as $queryKey => $queryValue) {
            if (empty($queryValue)) {
                unset($queryValues[$queryKey]);
            } else {
                $stringSuffix = self::MINIMAL_CHARACTER_LENGTH > strlen($queryValue) ? '' : '*';
                $queryValues[$queryKey] = $prefix . $queryValue . $stringSuffix;
                if ($stringSuffix !== '') {
                    $queryValues[$queryKey] .= ' ' . $prefix . $queryValue;
                }
                $queryValues[$queryKey] = $this->conditionManager->wrapBrackets($queryValues[$queryKey]);
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $this->conditionManager->wrapBrackets($queryValue);
    }

    /**
     * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
     *
     * @param string $value
     * @return string
     * @link http://lucene.apache.org/java/docs/queryparsersyntax.html#Escaping%20Special%20Characters
     */
    public function _escape($value)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
