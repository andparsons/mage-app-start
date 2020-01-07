<?php
namespace Magento\Setup\Module\Dependency\Report\Circular\Data;

use Magento\Setup\Module\Dependency\Report\Data\Config\AbstractConfig;

/**
 * Config
 *
 * @method \Magento\Setup\Module\Dependency\Report\Circular\Data\Module[] getModules()
 */
class Config extends AbstractConfig
{
    /**
     * {@inheritdoc}
     */
    public function getDependenciesCount()
    {
        $dependenciesCount = 0;
        foreach ($this->getModules() as $module) {
            $dependenciesCount += $module->getChainsCount();
        }
        return $dependenciesCount;
    }
}
