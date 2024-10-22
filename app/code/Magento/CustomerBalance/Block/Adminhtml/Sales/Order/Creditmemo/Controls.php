<?php
namespace Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Refund to customer balance functionality block
 *
 * @api
 * @since 100.0.2
 */
class Controls extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Check whether refund to customer balance is available
     *
     * @return bool
     */
    public function canRefundToCustomerBalance()
    {
        if ($this->_coreRegistry->registry('current_creditmemo')->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Check whether real amount can be refunded to customer balance
     *
     * @return bool
     */
    public function canRefundMoneyToCustomerBalance()
    {
        if (!$this->_coreRegistry->registry('current_creditmemo')->getGrandTotal()) {
            return false;
        }

        if ($this->_coreRegistry->registry('current_creditmemo')->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return true;
    }

    /**
     * Populate amount to be refunded to customer balance
     *
     * @return float
     */
    public function getReturnValue()
    {
        $creditMemo = $this->_coreRegistry->registry('current_creditmemo');

        $bsCustomerBalTotalRefunded = $creditMemo->getBsCustomerBalTotalRefunded();
        $customerBalance = !empty($bsCustomerBalTotalRefunded)
            ? $bsCustomerBalTotalRefunded
            : $creditMemo->getBaseCustomerBalanceReturnMax();
        // We want to subtract the Reward Points amount when returning to the customer
        $rewardAmount = $creditMemo->getBaseRewardCurrencyAmount();
        if ($rewardAmount > 0 && $rewardAmount <= $customerBalance) {
            $customerBalance -= $rewardAmount;
        }
        return $customerBalance ? $customerBalance : 0;
    }
}
