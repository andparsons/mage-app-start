<?php

namespace Magento\Paypal\Test\Block\Sandbox;

use Magento\Mtf\Block\Form;

/**
 * Login to PayPal.
 */
class ExpressLogin extends Form
{
    /**
     * Login button on PayPal side.
     *
     * @var string
     */
    protected $loginButton = '#btnLogin';

    /**
     * Login to PayPal Sandbox.
     *
     * @return void
     */
    public function sandboxLogin()
    {
        $this->_rootElement->find($this->loginButton)->click();
    }
}
