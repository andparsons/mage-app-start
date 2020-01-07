<?php
namespace Magento\Solr\SearchAdapter;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Dimension;
use Solarium\QueryType\Select\Query\Query;

class Dimensions
{
    const STORE_FIELD_NAME = 'store_id';

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param FieldMapperInterface $fieldMapper
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        FieldMapperInterface $fieldMapper
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->fieldMapper = $fieldMapper;
    }

    /**
     * @param Dimension[] $dimensions
     * @param Query $selectQuery
     * @return void
     */
    public function build(array $dimensions, Query $selectQuery)
    {
        foreach ($dimensions as $dimension) {
            $this->addFilterToQuery($dimension, $selectQuery);
        }
    }

    /**
     * @param Dimension $dimension
     * @param Query $selectQuery
     * @return void
     */
    private function addFilterToQuery(Dimension $dimension, Query $selectQuery)
    {
        $field = $dimension->getName();
        $value = $dimension->getValue();

        if ('scope' === $field) {
            $field = self::STORE_FIELD_NAME;
            $value = $this->scopeResolver->getScope($value)
                ->getId();
        }
        $field = $this->fieldMapper->getFieldName($field);

        $selectQuery->createFilterQuery($field)
            ->setQuery("{$field}:%1%", [$value]);
    }
}
