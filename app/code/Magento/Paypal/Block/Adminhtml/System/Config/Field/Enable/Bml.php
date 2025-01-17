<?php
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class Bml
 */
class Bml extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'bml';
    }
}
