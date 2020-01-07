<?php

namespace Magento\Sales\Test\Block\Adminhtml\Order\Shipment;

use Magento\Mtf\Block\Block;

/**
 * Class Totals
 * Shipment totals block
 *
 */
class Totals extends Block
{
    /**
     * Submit Shipment selector
     *
     * @var string
     */
    protected $submit = '[data-ui-id="order-items-submit-button"]';

    /**
     * Ship order
     */
    public function submit()
    {
        $this->_rootElement->find($this->submit)->click();
    }
}
