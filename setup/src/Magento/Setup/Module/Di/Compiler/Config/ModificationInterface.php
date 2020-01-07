<?php

namespace Magento\Setup\Module\Di\Compiler\Config;

/**
 * Interface \Magento\Setup\Module\Di\Compiler\Config\ModificationInterface
 *
 */
interface ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config);
}
