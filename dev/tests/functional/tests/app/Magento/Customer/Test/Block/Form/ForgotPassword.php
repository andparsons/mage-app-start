<?php

namespace Magento\Customer\Test\Block\Form;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 */
class ForgotPassword extends Form
{
    /**
     * 'Submit' form button
     *
     * @var string
     */
    protected $submit = '.action.submit';

    /**
     * Fill and submit form
     *
     * @param Customer $fixture
     */
    public function resetForgotPassword(Customer $fixture)
    {
        $this->fill($fixture);
        $this->_rootElement->find($this->submit, Locator::SELECTOR_CSS)->click();
    }
}
