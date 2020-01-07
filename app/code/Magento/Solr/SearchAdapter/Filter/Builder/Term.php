<?php
namespace Magento\Solr\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Term as TermFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use Magento\Solr\SearchAdapter\Filter\Builder;
use Solarium\QueryType\Select\Query\Query;

class Term implements FilterInterface
{
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
     * @param RequestFilterInterface|TermFilterRequest $filter
     * @return string
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        return implode(
            ' ' . Query::QUERY_OPERATOR_OR . ' ',
            array_map(
                function ($value) use ($filter) {
                    return sprintf(
                        '%s:"%s"',
                        $this->mapper->getFieldName($filter->getField()),
                        $value
                    );
                },
                (array)$filter->getValue()
            )
        );
    }
}
