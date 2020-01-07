<?php
namespace Magento\Cybersource\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CanVoidHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CanVoidHandler implements ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        $paymentDO = SubjectReader::readPayment($subject);

        $payment = $paymentDO->getPayment();
        return !$payment instanceof Payment || !(bool)$payment->getAmountPaid();
    }
}
