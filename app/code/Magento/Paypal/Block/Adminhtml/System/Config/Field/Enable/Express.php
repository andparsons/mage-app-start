<?php
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class Express
 */
class Express extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'express';
    }
}
