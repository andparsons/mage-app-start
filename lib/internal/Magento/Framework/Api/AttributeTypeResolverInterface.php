<?php

namespace Magento\Framework\Api;

/**
 * Interface \Magento\Framework\Api\AttributeTypeResolverInterface
 *
 */
interface AttributeTypeResolverInterface
{
    /**
     * Resolve attribute type
     *
     * @param string $attributeCode
     * @param object $value
     * @param string $context
     * @return string
     */
    public function resolveObjectType($attributeCode, $value, $context);
}
