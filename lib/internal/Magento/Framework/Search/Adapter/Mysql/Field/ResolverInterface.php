<?php
namespace Magento\Framework\Search\Adapter\Mysql\Field;

/**
 * MySQL search field resolver.
 *
 * @deprecated 102.0.0
 * @see \Magento\ElasticSearch
 */
interface ResolverInterface
{
    /**
     * Resolve field.
     *
     * @param array $fields
     * @return FieldInterface[]
     */
    public function resolve(array $fields);
}
