<?php
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class Payment
 */
class Payment extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'payment';
    }
}
