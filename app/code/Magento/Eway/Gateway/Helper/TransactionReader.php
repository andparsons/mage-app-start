<?php
namespace Magento\Eway\Gateway\Helper;

use Magento\Eway\Gateway\Request\TransactionIdDataBuilder;
use Magento\Eway\Gateway\Validator\AbstractResponseValidator;

/**
 * Class TransactionReader
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class TransactionReader
{
    /**
     * Read access code from transaction data
     *
     * @param array $transactionData
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function readAccessCode(array $transactionData)
    {
        if (empty($transactionData[AbstractResponseValidator::ACCESS_CODE])) {
            throw new \InvalidArgumentException('Access code should be provided');
        }

        return $transactionData[AbstractResponseValidator::ACCESS_CODE];
    }

    /**
     * Read transaction id from transaction data
     *
     * @param array $transactionData
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function readTransactionId(array $transactionData)
    {
        if (!isset($transactionData[TransactionIdDataBuilder::TRANSACTION_ID])) {
            throw new \InvalidArgumentException('Transaction id should be provided');
        }

        return $transactionData[TransactionIdDataBuilder::TRANSACTION_ID];
    }
}
