<?php

namespace Magento\CompanyCredit\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Add label for refund to Company Credit.
 */
class ItemsPlugin
{
    /**
     * Before toHtml.
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items $subject
     * @return void
     */
    public function beforeToHtml(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items $subject)
    {
        $order = $subject->getOrder();
        if ($order->getPayment()->getMethod() === CompanyCreditPaymentConfigProvider::METHOD_NAME) {
            $refundBtn = $subject->getChildBlock('submit_offline');
            $refundBtn->setLabel(__('Refund to Company Credit'));
        }
    }
}
