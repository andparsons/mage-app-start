<?php
namespace Magento\Solr\SearchAdapter;

/**
 * Interface \Magento\Solr\SearchAdapter\FieldMapperInterface
 *
 */
interface FieldMapperInterface
{
    const TYPE_QUERY = 'text';
    const TYPE_SORT = 'sort';
    const TYPE_FILTER = 'default';

    /**
     * Get field name
     *
     * @param string $attributeCode
     * @param array $context
     * @return mixed
     */
    public function getFieldName($attributeCode, $context = []);
}
