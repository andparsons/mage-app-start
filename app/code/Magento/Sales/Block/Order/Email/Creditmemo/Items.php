<?php
namespace Magento\Sales\Block\Order\Email\Creditmemo;

/**
 * Sales Order Email creditmemo items
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     * @return void
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getCreditmemo());
    }
}
