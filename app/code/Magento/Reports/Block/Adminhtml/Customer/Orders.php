<?php
namespace Magento\Reports\Block\Adminhtml\Customer;

/**
 * Backend customers by orders report content block
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Define children block group
     *
     * @var string
     */
    protected $_blockGroup = 'Magento_Reports';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_customer_orders';
        $this->_headerText = __('Customers by number of orders');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
