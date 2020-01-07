<?php
namespace Magento\Cybersource\Gateway\Response\Soap;

use Magento\Sales\Model\Order\Payment;

/**
 * Class RefundTransactionHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class RefundTransactionHandler extends VoidTransactionHandler
{
    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return !(bool)$orderPayment->getCreditmemo()->getInvoice()->canRefund();
    }
}
