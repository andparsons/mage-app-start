<?php
namespace Magento\Catalog\Model\ProductOptions;

/**
 * Interface \Magento\Catalog\Model\ProductOptions\ConfigInterface
 *
 */
interface ConfigInterface
{
    /**
     * Get configuration of product type by name
     *
     * @param string $name
     * @return array
     */
    public function getOption($name);

    /**
     * Get configuration of all registered product types
     *
     * @return array
     */
    public function getAll();
}
