<?php
declare(strict_types=1);

namespace Magento\Elasticsearch6\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\DefaultResolver as Base;

/**
 * Default name resolver.
 */
class DefaultResolver extends Base
{
    /**
     * Get field name.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        $fieldName = parent::getFieldName($attribute, $context);

        if ($fieldName === '_all') {
            $fieldName = '_search';
        }

        return $fieldName;
    }
}
