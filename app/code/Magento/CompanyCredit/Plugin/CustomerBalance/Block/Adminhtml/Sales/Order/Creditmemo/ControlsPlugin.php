<?php

namespace Magento\CompanyCredit\Plugin\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * This plugin hide Refund to Store Credit field for Payment on Account method.
 */
class ControlsPlugin
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->coreRegistry = $registry;
    }

    /**
     * Hide Refund to Store Credit field for Payment on Account method.
     *
     * @param \Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\Controls $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanRefundToCustomerBalance(
        \Magento\CustomerBalance\Block\Adminhtml\Sales\Order\Creditmemo\Controls $subject,
        $result
    ) {
        $order = $this->coreRegistry->registry('current_creditmemo')->getOrder();

        return $result
            && $order->getPayment()->getMethod() !== CompanyCreditPaymentConfigProvider::METHOD_NAME;
    }
}
