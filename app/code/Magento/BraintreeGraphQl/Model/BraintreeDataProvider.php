<?php
declare(strict_types=1);

namespace Magento\BraintreeGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Format Braintree input into value expected when setting payment method
 */
class BraintreeDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'braintree';

    /**
     * Format Braintree input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $args): array
    {
        if (!isset($args[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "braintree" for "payment_method" is missing.')
            );
        }

        if (!isset($args[self::PATH_ADDITIONAL_DATA]['payment_method_nonce'])) {
            throw new GraphQlInputException(
                __('Required parameter "payment_method_nonce" for "braintree" is missing.')
            );
        }

        if (!isset($args[self::PATH_ADDITIONAL_DATA]['is_active_payment_token_enabler'])) {
            throw new GraphQlInputException(
                __('Required parameter "is_active_payment_token_enabler" for "braintree" is missing.')
            );
        }

        return $args[self::PATH_ADDITIONAL_DATA];
    }
}
