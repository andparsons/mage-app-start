<?php

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;

/**
 * 404 error backend block.
 */
class Error extends Block
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
