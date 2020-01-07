<?php

/**
 * Adminhtml company index page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Company\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify header & button labels
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'company_index';
        $this->_headerText = __('Companies');
        $this->_addButtonLabel = __('Add New Company');
        parent::_construct();
    }
}
