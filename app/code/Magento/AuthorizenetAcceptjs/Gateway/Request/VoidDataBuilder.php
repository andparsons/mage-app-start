<?php

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the meta transaction information to the request
 */
class VoidDataBuilder implements BuilderInterface
{
    private const REQUEST_TYPE_VOID = 'voidTransaction';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $transactionData = [];

        if ($payment instanceof Payment) {
            $authorizationTransaction = $payment->getAuthorizationTransaction();
            $refId = $authorizationTransaction->getAdditionalInformation('real_transaction_id');
            if (empty($refId)) {
                $refId = $authorizationTransaction->getParentTxnId();
            }

            $transactionData['transactionRequest'] = [
                'transactionType' => self::REQUEST_TYPE_VOID,
                'refTransId' => $refId
            ];
        }

        return $transactionData;
    }
}
