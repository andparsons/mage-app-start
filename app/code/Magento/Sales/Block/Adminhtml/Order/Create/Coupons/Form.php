<?php
namespace Magento\Sales\Block\Adminhtml\Order\Create\Coupons;

/**
 * Adminhtml sales order create coupons form block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_coupons_form');
    }

    /**
     * Get coupon code
     *
     * @return string
     */
    public function getCouponCode()
    {
        return $this->getParentBlock()->getQuote()->getCouponCode();
    }
}
