<?php

namespace Magento\AdminNotification\Test\Block\System\Messages;

use Magento\Mtf\Block\Block;

/**
 * System message block.
 */
class System extends Block
{
    /**
     * Get block text content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_rootElement->getText();
    }
}
