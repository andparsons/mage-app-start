<?php

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the basic payment information to the request
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $data = [];

        if ($payment instanceof Payment) {
            $dataDescriptor = $payment->getAdditionalInformation('opaqueDataDescriptor');
            $dataValue = $payment->getAdditionalInformation('opaqueDataValue');

            $data['transactionRequest']['payment'] = [
                'opaqueData' => [
                    'dataDescriptor' => $dataDescriptor,
                    'dataValue' => $dataValue
                ]
            ];
        }

        return $data;
    }
}
