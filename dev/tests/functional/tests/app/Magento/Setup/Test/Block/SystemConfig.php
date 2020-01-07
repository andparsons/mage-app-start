<?php

namespace Magento\Setup\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * System Config block.
 */
class SystemConfig extends Block
{
    /**
     * @var string
     */
    protected $systemConfig = '.setup-home-item-configuration';

    /**
     * Click on 'System Configuration' button.
     *
     * @return void
     */
    public function clickSystemConfig()
    {
        $this->_rootElement->find($this->systemConfig, Locator::SELECTOR_CSS)->click();
    }
}
