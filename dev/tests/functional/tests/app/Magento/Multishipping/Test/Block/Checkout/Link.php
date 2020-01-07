<?php

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;

/**
 * Multishipping cart link
 */
class Link extends Block
{
    /**
     * Press 'Proceed to Checkout' link
     *
     * @return void
     */
    public function multipleAddressesCheckout()
    {
        $this->_rootElement->click();
    }
}
