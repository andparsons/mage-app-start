<?php
namespace Magento\Reports\Block\Adminhtml\Customer;

/**
 * Backend new accounts report page content block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Accounts extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_customer_accounts';
        $this->_headerText = __('New Accounts');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
