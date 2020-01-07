<?php
declare(strict_types=1);

namespace Magento\Eway\Gateway\Validator;

use Magento\Eway\Gateway\Request\RequestDataBuilder;
use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class AbstractResponseValidator
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
abstract class AbstractResponseValidator extends AbstractValidator
{
    /**
     * A comma separated list of any error encountered, these can be looked up in the Response Codes section
     */
    const ERRORS = 'Errors';

    /**
     * This set of fields contains the details of the merchant’s customer
     */
    const CUSTOMER = 'Customer';

    /**
     * The card details section is within the Customer section
     */
    const CARD_DETAILS = 'CardDetails';

    /**
     * This set of fields contains the details of the payment which was processed
     */
    const PAYMENT = 'Payment';

    /**
     * Refund data block
     */
    const REFUND = 'Refund';

    /**
     * The amount that was authorised for this transaction
     */
    const TOTAL_AMOUNT = 'TotalAmount';

    /**
     * The transaction type that this transaction was processed under
     * One of: Purchase, MOTO, Recurring
     */
    const TRANSACTION_TYPE = 'TransactionType';

    /**
     * A Boolean value that indicates whether the transaction was successful or not
     */
    const TRANSACTION_STATUS = 'TransactionStatus';

    /**
     * A unique identifier that represents the transaction in eWAY’s system
     */
    const TRANSACTION_ID = 'TransactionID';

    /**
     * A code that describes the result of the action performed
     */
    const RESPONSE_MESSAGE = 'ResponseMessage';

    /**
     * The two digit response code returned from the bank
     */
    const RESPONSE_CODE = 'ResponseCode';

    /**
     * Value of response code
     *
     * @deprecated
     * @see self::$acceptableCodes
     */
    const RESPONSE_CODE_ACCEPT = '00';

    /**
     * The authorisation code for this transaction as returned by the bank
     */
    const AUTHORISATION_CODE = 'AuthorisationCode';

    /**
     * A unique Access Code that is used to identify this transaction with Rapid API.
     * This code will need to be present for all future requests associated with this transaction
     */
    const ACCESS_CODE = 'AccessCode';

    /**
     * A masked echo of the card number
     */
    const CARD_NUMBER = 'Number';

    /**
     * An echo of the month that the card expires
     */
    const CARD_EXPIRY_MONTH = 'ExpiryMonth';

    /**
     * An echo of the year that the card expires
     */
    const CARD_EXPIRY_YEAR = 'ExpiryYear';

    /**
     * The list of acceptable response codes.
     *
     * @var array
     */
    private static $acceptableCodes = ['00', '08'];

    /**
     * Checks if response doesn't contain errors.
     *
     * @param array $response
     * @return bool
     */
    protected function validateErrors(array $response)
    {
        return empty($response[self::ERRORS]);
    }

    /**
     * Checks if response contains correct amount.
     *
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        return isset($response[self::PAYMENT][self::TOTAL_AMOUNT])
        && (string)($response[self::PAYMENT][self::TOTAL_AMOUNT] / 100) === (string)$amount;
    }

    /**
     * Checks if transaction type is `Purchase`.
     *
     * @param array $response
     * @return bool
     */
    protected function validateTransactionType(array $response)
    {
        return isset($response[self::TRANSACTION_TYPE])
        && $response[self::TRANSACTION_TYPE] === RequestDataBuilder::PURCHASE;
    }

    /**
     * Checks if transaction status is successful.
     *
     * @param array $response
     * @return bool
     */
    protected function validateTransactionStatus(array $response)
    {
        return isset($response[self::TRANSACTION_STATUS])
        && $response[self::TRANSACTION_STATUS] === true;
    }

    /**
     * Checks if transaction ID is present in response.
     *
     * @param array $response
     * @return bool
     */
    protected function validateTransactionId(array $response)
    {
        return !empty($response[self::TRANSACTION_ID]);
    }

    /**
     * Validates acceptable response codes.
     *
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return isset($response[self::RESPONSE_CODE])
            && in_array($response[self::RESPONSE_CODE], self::$acceptableCodes);
    }

    /**
     * Checks if response contains a message.
     *
     * @param array $response
     * @return bool
     */
    protected function validateResponseMessage(array $response)
    {
        return !empty($response[self::RESPONSE_MESSAGE]);
    }

    /**
     * Checks if response contains authorization code.
     *
     * @param array $response
     * @return bool
     */
    protected function validateAuthorisationCode(array $response)
    {
        return !empty($response[self::AUTHORISATION_CODE]);
    }
}
