<?php
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\ConfigLoaderInterface
 *
 */
interface ConfigLoaderInterface
{
    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array
     */
    public function load($area);
}
