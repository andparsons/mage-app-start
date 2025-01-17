<?php
namespace Magento\Reports\Block\Adminhtml\Review;

/**
 * Adminhtml report review product blocks content block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Product extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_review_product';
        $this->_headerText = __('Products Reviews');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
