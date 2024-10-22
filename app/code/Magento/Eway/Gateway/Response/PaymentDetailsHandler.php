<?php
namespace Magento\Eway\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Eway\Gateway\Validator\Direct\ResponseValidator;

/**
 * Class PaymentDetailsHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var array
     */
    private $additionalInformationMapping = [
        'transaction_type' => ResponseValidator::TRANSACTION_TYPE,
        'transaction_id' => ResponseValidator::TRANSACTION_ID,
        'response_code' => ResponseValidator::RESPONSE_CODE,
    ];

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        $payment->setTransactionId($response[ResponseValidator::TRANSACTION_ID]);
        $payment->setLastTransId($response[ResponseValidator::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(false);

        foreach ($this->additionalInformationMapping as $informationKey => $responseKey) {
            if (isset($response[$responseKey])) {
                $payment->setAdditionalInformation($informationKey, $response[$responseKey]);
            }
        }
    }
}
