<?php
namespace Magento\Reports\Block\Adminhtml\Shopcart;

/**
 * Adminhtml Shopping cart customers report page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_shopcart_customer';
        $this->_headerText = __('Customers');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
