<?php

namespace Magento\CompanyPayment\Block\System\Config\Form;

/**
 * Class PaymentMethodsFieldset.
 */
class PaymentMethodsFieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _getFooterHtml($element)
    {
        return parent::_getFooterHtml($element)
        . $this->getLayout()
            ->createBlock(\Magento\CompanyPayment\Block\System\Config\PaymentMethods::class)
            ->toHtml();
    }
}
