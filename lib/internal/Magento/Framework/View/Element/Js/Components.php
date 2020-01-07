<?php
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\App\State;
use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 100.0.2
 */
class Components extends Template
{
    /**
     * Developer mode
     *
     * @return bool
     */
    public function isDeveloperMode()
    {
        return $this->_appState->getMode() == State::MODE_DEVELOPER;
    }
}
