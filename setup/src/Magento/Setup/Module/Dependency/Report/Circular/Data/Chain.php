<?php
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

/**
 * Chain
 */
class Chain
{
    /**
     * Chain construct
     *
     * @param array $modules
     */
    public function __construct($modules)
    {
        $this->modules = $modules;
    }

    /**
     * Get modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }
}
