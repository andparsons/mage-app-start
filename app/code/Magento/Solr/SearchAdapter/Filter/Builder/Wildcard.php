<?php
namespace Magento\Solr\SearchAdapter\Filter\Builder;

use Magento\Framework\Search\Request\Filter\Wildcard as WildcardFilterRequest;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Solr\SearchAdapter\FieldMapperInterface;

class Wildcard implements FilterInterface
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
     * @param RequestFilterInterface|WildcardFilterRequest $filter
     * @return string
     */
    public function buildFilter(RequestFilterInterface $filter)
    {
        return sprintf(
            '%s:(*%s*)',
            $this->mapper->getFieldName($filter->getField()),
            $filter->getValue()
        );
    }
}
