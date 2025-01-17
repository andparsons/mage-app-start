<?php
namespace Magento\Reports\Block\Adminhtml\Refresh;

/**
 * Report Refresh statistic container
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Statistics extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Modify Header and remove button "Add"
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_refresh_statistics';
        $this->_headerText = __('Refresh Statistics');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
