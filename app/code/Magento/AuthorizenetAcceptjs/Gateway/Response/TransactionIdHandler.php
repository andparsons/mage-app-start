<?php

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Response;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Processes transaction id for the payment
 */
class TransactionIdHandler implements HandlerInterface
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
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $transactionResponse = $response['transactionResponse'];

        if ($payment instanceof Payment) {
            if (!$payment->getParentTransactionId()
                || $transactionResponse['transId'] != $payment->getParentTransactionId()
            ) {
                $payment->setTransactionId($transactionResponse['transId']);
            }
            $payment->setTransactionAdditionalInfo(
                'real_transaction_id',
                $transactionResponse['transId']
            );
        }
    }
}
