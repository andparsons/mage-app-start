<?php

namespace Magento\CompanyCredit\Plugin\Sales\Model\Order;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Order items quantity validation.
 */
class InvoiceQuantityValidatorPlugin
{
    /**
     * Around validate.
     *
     * @param \Magento\Sales\Model\Order\InvoiceQuantityValidator $subject
     * @param \Closure $method
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Framework\Phrase[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate(
        \Magento\Sales\Model\Order\InvoiceQuantityValidator $subject,
        \Closure $method,
        \Magento\Sales\Model\Order\Invoice $invoice
    ) {
        $result = $method($invoice);
        $payment = $invoice->getOrder()->getPayment();
        if ($payment->getMethod() === CompanyCreditPaymentConfigProvider::METHOD_NAME && empty($result)) {
            $quantities = $this->getOrderQty($invoice->getOrder());
            if ($quantities) {
                foreach ($invoice->getItems() as $invoiceItem) {
                    if (isset($quantities[$invoiceItem->getOrderItemId()])) {
                        if ($quantities[$invoiceItem->getOrderItemId()] <= $invoiceItem->getQty()) {
                            unset($quantities[$invoiceItem->getOrderItemId()]);
                        }
                    }
                }
                if ($quantities) {
                    $result[] = __(
                        'An invoice for partial quantities cannot be issued for this order. '
                        . 'To continue, change the specified quantity to the full quantity.'
                    );
                }
            }
        }
        return $result;
    }

    /**
     * Get order items quantities.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getOrderQty(\Magento\Sales\Model\Order $order)
    {
        $quantities = [];
        foreach ($order->getItems() as $item) {
            if (!$item->isDummy()) {
                $quantities[$item->getItemId()] = $item->getQtyOrdered();
            }
        }
        return $quantities;
    }
}
