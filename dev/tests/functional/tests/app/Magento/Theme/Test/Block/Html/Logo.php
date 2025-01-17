<?php

namespace Magento\Theme\Test\Block\Html;

use Magento\Mtf\Block\Block;

/**
 * Logo block.
 */
class Logo extends Block
{
    /**
     * Click on logo element.
     *
     * @return void
     */
    public function clickOnLogo()
    {
        $this->_rootElement->click();
    }
}
