<?php
namespace Magento\Sales\Block\Adminhtml;

/**
 * Adminhtml sales invoices block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Invoice extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_invoice';
        $this->_blockGroup = 'Magento_Sales';
        $this->_headerText = __('Invoices');
        parent::_construct();
        $this->buttonList->remove('add');
    }

    /**
     * Get payment info html
     *
     * @return string
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }
}
