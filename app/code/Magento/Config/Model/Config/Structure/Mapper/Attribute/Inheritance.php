<?php

/**
 * System Configuration Attribute Inheritance Mapper
 */
namespace Magento\Config\Model\Config\Structure\Mapper\Attribute;

/**
 * @api
 * @since 100.0.2
 */
class Inheritance implements \Magento\Config\Model\Config\Structure\MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data)
    {
        return $data;
    }
}
