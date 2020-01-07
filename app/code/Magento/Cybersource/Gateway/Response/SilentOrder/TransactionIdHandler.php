<?php
namespace Magento\Cybersource\Gateway\Response\SilentOrder;

use Magento\Cybersource\Gateway\Response;

/**
 * Class TransactionIdHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransactionIdHandler extends Response\TransactionIdHandler
{
    /**
     * Transaction Id key
     */
    const TRANSACTION_ID = 'transaction_id';

    /**
     * Returns field name containing transaction id
     *
     * @return string
     */
    protected function getTransactionIdField()
    {
        return self::TRANSACTION_ID;
    }
}
