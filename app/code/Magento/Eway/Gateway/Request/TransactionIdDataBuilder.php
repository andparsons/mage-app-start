<?php
namespace Magento\Eway\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionIdDataBuilder
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransactionIdDataBuilder implements BuilderInterface
{
    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = 'TransactionId';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        return [
            self::TRANSACTION_ID => $paymentDO->getPayment()->getParentTransactionId()
        ];
    }
}
