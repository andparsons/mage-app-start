<?php
namespace Magento\Setup\Module\Dependency\Report\Data;

/**
 * Config
 */
interface ConfigInterface
{
    /**
     * Get modules
     *
     * @return array
     */
    public function getModules();

    /**
     * Get total dependencies count
     *
     * @return int
     */
    public function getDependenciesCount();
}
