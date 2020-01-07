<?php

namespace Magento\CompanyCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;
use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Observer for event sales_order_invoice_register.
 */
class InvoiceRegisterObserver implements ObserverInterface
{
    /**
     * Execute observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        /** @var Invoice $invoice */
        $invoice = $observer->getInvoice();
        if ($order->getPayment()->getMethod() == CompanyCreditPaymentConfigProvider::METHOD_NAME) {
            if ($order->getStatus()) {
                $order->addStatusHistoryComment(
                    __('Invoice created for %1', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal())),
                    Order::STATE_PROCESSING
                );
            } else {
                $order->setCustomerNote(
                    __('Invoice created for %1', $order->getBaseCurrency()->formatTxt($invoice->getBaseGrandTotal()))
                );
            }
        }
    }
}
