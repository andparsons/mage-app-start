<?php

/**
 * System Configuration Converter Mapper Interface
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 100.0.2
 */
interface MapperInterface
{
    /**
     * Apply map
     *
     * @param array $data
     * @return array
     */
    public function map(array $data);
}
