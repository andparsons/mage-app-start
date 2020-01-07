<?php
namespace Magento\Cybersource\Gateway\Response\SilentOrder;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Cybersource\Gateway\Request\SilentOrder\TransactionDataBuilder;
use Magento\Sales\Model\Order\Payment;

/**
 * Class ReferenceNumberHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class ReferenceNumberHandler implements HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $referenceNumberField = 'req_' . TransactionDataBuilder::REFERENCE_NUMBER;

        if (!isset($response[$referenceNumberField])) {
            return;
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        $paymentDO->getPayment()
            ->setAdditionalInformation(
                TransactionDataBuilder::REFERENCE_NUMBER,
                $response[$referenceNumberField]
            );
    }
}
