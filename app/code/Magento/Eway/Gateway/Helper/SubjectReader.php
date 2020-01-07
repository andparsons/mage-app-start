<?php
namespace Magento\Eway\Gateway\Helper;

/**
 * Class SubjectReader
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class SubjectReader
{
    /**
     * Reads access code from subject
     *
     * @param array $subject
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function readAccessCode(array $subject)
    {
        if (empty($subject['access_code'])) {
            throw new \InvalidArgumentException('Access code should be provided.');
        }

        return $subject['access_code'];
    }

    /**
     * Read transaction id from subject
     *
     * @param array $subject
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function readTransactionId(array $subject)
    {
        if (!isset($subject['request']['transaction_id'])
            || !is_string($subject['request']['transaction_id'])
        ) {
            throw new \InvalidArgumentException('Transaction id does not exist');
        }

        return $subject['request']['transaction_id'];
    }
}
