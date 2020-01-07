<?php

/**
 * Tax Total Row Renderer
 */
namespace Magento\Tax\Block\Checkout;

class Tax extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Tax::checkout/tax.phtml';
}
