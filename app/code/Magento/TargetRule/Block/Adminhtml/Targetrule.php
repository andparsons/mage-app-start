<?php
namespace Magento\TargetRule\Block\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
class Targetrule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize invitation manage page
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_targetrule';
        $this->_blockGroup = 'Magento_TargetRule';
        $this->_headerText = __('Related Products Rule');
        $this->_addButtonLabel = __('Add Rule');
        parent::_construct();
    }
}
