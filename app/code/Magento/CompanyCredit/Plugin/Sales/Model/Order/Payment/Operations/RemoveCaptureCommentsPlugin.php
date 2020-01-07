<?php

namespace Magento\CompanyCredit\Plugin\Sales\Model\Order\Payment\Operations;

/**
 * Remove capture comments for 'companycredit' payment method.
 */
class RemoveCaptureCommentsPlugin
{
    /**
     * Remove capture comments if payment method is 'companycredit'.
     *
     * Capture operation adds comments to order. But we must not save those comments for 'companycredit' payment method.
     * So we obtain order comments before capture operation was executed, execute the operation
     * and then set old comments to the order.
     *
     * @param \Magento\Sales\Model\Order\Payment\Operations\CaptureOperation $subject
     * @param \Closure $proceed
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @param \Magento\Sales\Api\Data\InvoiceInterface|null $invoice
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCapture(
        \Magento\Sales\Model\Order\Payment\Operations\CaptureOperation $subject,
        $proceed,
        \Magento\Sales\Api\Data\OrderPaymentInterface $payment,
        $invoice
    ) {
        if (\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME == $payment->getMethod()) {
            /**
             * @var $payment \Magento\Sales\Model\Order\Payment
             */
            $statusHistories = $payment->getOrder()->getStatusHistories();
            $result = $proceed($payment, $invoice);
            $payment->getOrder()->setStatusHistories($statusHistories);
        } else {
            $result = $proceed($payment, $invoice);
        }

        return $result;
    }
}
