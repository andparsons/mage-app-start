<?php
namespace Magento\Cybersource\Gateway\Response\Soap;

use Magento\Cybersource\Gateway\Response;

/**
 * Class RequestIdHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class RequestIdHandler extends Response\TransactionIdHandler
{
    /**
     * Request id key
     */
    const REQUEST_ID = 'requestID';

    /**
     * Returns field name containing transaction id
     *
     * @return string
     */
    protected function getTransactionIdField()
    {
        return self::REQUEST_ID;
    }
}
