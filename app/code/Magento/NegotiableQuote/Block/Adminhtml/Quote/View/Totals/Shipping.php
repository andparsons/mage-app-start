<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Totals;

/**
 * Class Shipping
 *
 * @api
 * @since 100.0.0
 */
class Shipping extends AbstractTotals
{
    /**
     * @var string
     */
    protected $code = 'shipping';

    /**
     * Can edit
     *
     * @return bool
     */
    public function canEdit()
    {
        return parent::canEdit()
        && $this->getParentBlock()->getQuote()->getShippingAddress()
        && $this->getParentBlock()->getQuote()->getShippingAddress()->getPostcode();
    }
}
