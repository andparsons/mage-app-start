<?php
namespace Magento\Solr\SearchAdapter;

use Magento\Framework\App\ResourceConnection;

class ConditionManager
{
    const CONDITION_PATTERN_SIMPLE = '%s:"%s"';
    const CONDITION_PATTERN_ARRAY = '%s:("%s")';

    /**
     * @param string $query
     * @return string
     */
    public function wrapBrackets($query)
    {
        return empty($query)
            ? $query
            : '(' . $query . ')';
    }

    /**
     * @param string[] $queries
     * @param string $unionOperator
     * @return string
     */
    public function combineQueries(array $queries, $unionOperator)
    {
        return implode(
            ' ' . $unionOperator . ' ',
            array_filter($queries, 'strlen')
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return string
     */
    public function generateCondition($field, $value)
    {
        return sprintf(
            (is_array($value) ? self::CONDITION_PATTERN_ARRAY : self::CONDITION_PATTERN_SIMPLE),
            $field,
            $value
        );
    }

    /**
     * @param string $condition
     * @return string
     */
    public function addNegation($condition)
    {
        return "*:* NOT {$condition}";
    }
}
