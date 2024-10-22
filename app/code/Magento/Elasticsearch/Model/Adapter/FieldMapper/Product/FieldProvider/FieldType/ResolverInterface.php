<?php
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;

/**
 * Field type resolver interface.
 */
interface ResolverInterface
{
    /**
     * Get field type.
     *
     * @param AttributeAdapter $attribute
     * @return string
     */
    public function getFieldType(AttributeAdapter $attribute): ?string;
}
