<?php
namespace Magento\Cybersource\Gateway\Request\Soap;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class CaptureDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CaptureDataBuilder implements BuilderInterface
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        return [
            'ccCaptureService' => [
                'run' => 'true',
                'authRequestID' => $paymentInfo->getParentTransactionId()
            ],
            'purchaseTotals' => [
                'currency' => $paymentDO->getOrder()->getCurrencyCode(),
                'grandTotalAmount' => SubjectReader::readAmount($buildSubject)
             ]
        ];
    }
}
