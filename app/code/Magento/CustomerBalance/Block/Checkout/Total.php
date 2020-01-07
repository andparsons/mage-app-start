<?php

/**
 * Customer balance total block for checkout
 *
 */
namespace Magento\CustomerBalance\Block\Checkout;

class Total extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * Custom constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_isScopePrivate = true;
    }

    /**
     * @var string
     */
    protected $_template = 'Magento_CustomerBalance::checkout/total.phtml';
}
