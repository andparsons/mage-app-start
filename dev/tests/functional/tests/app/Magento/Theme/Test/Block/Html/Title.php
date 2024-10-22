<?php

namespace Magento\Theme\Test\Block\Html;

use Magento\Mtf\Block\Block;

/**
 * Page title block
 */
class Title extends Block
{
    /**
     * Get title of current page
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->getText();
    }
}
