<?php
namespace Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Renderer;

/**
 * Description renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Html extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Return data "as is", don't escape HTML
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getData($this->getColumn()->getIndex());
    }
}
