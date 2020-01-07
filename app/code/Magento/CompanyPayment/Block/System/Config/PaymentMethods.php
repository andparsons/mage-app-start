<?php

namespace Magento\CompanyPayment\Block\System\Config;

/**
 * Class PaymentMethods.
 */
class PaymentMethods extends \Magento\Backend\Block\Template
{
    /**
     * Define block template.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magento_CompanyPayment::payment/methods.phtml');
        parent::_construct();
    }
}
