<?php
namespace Magento\Cybersource\Gateway\Helper;

/**
 * Class SilentOrderHelper
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class SilentOrderHelper
{
    /**
     * Signs fields
     *
     * @param array $fieldsToSign
     * @param string $key
     * @return string
     * phpcs:disable Magento2.Functions.StaticFunction
     */
    public static function signFields(array $fieldsToSign, $key)
    {
        array_walk(
            $fieldsToSign,
            function (&$value, $key) {
                $value = sprintf('%s=%s', $key, (string)$value);
            }
        );

        return base64_encode(
            hash_hmac(
                'sha256',
                implode(',', $fieldsToSign),
                $key,
                true
            )
        );
    }
}
