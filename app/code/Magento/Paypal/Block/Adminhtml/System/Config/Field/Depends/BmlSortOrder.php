<?php
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Depends;

use Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable;

/**
 * Class BmlSortOrder
 */
class BmlSortOrder extends AbstractEnable
{
    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    protected function getDataAttributeName()
    {
        return 'bml-sort-order';
    }
}
