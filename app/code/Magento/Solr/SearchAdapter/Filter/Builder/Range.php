<?php
namespace Magento\Solr\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Solarium\QueryType\Select\Query\Query;

class Range implements FilterInterface
{
    const EMPTY_VALUE = '*';

    /**
     * @var FieldMapperInterface
     */
    private $mapper;

    /**
     * @param FieldMapperInterface $mapper
     */
    public function __construct(FieldMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param RequestFilterInterface|RangeFilterRequest $filter
     * @return Query
     */
    public function buildFilter(
        RequestFilterInterface $filter
    ) {
        return sprintf(
            '%s:[%s TO %s]',
            $this->mapper->getFieldName($filter->getField()),
            $filter->getFrom() ?: self::EMPTY_VALUE,
            $filter->getTo() ?: self::EMPTY_VALUE
        );
    }
}
