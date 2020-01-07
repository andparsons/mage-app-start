<?php
namespace Magento\Cybersource\Gateway\Request\Soap;

use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Cybersource\Gateway\Response\SilentOrder\TransactionIdHandler;

/**
 * Class SubscriptionDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class SubscriptionDataBuilder implements BuilderInterface
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
            'paySubscriptionCreateService' => [
                'run' => 'true',
                'paymentRequestID' => $paymentInfo->getAdditionalInformation(
                    TransactionIdHandler::TRANSACTION_ID
                )
            ],
            'recurringSubscriptionInfo' => [
                'frequency' => 'on-demand'
            ]
        ];
    }
}
