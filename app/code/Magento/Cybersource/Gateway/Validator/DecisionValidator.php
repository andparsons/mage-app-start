<?php
namespace Magento\Cybersource\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class DecisionValidator.
 *
 * @package Magento\Cybersource\Gateway\Validator
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class DecisionValidator extends AbstractValidator
{
    const DECISION = 'decision';

    const REASON_CODE = 'reason_code';

    /**
     *  Successful transaction.
     */
    const REASON_SUCCESSFUL = 100;

    /**
     *  The authorization has already been reversed.
     */
    const REASON_AUTH_REVERSED = 237;

    /**
     * The transaction has already been settled or reversed.
     */
    const REASON_TRANSACTION_REVERSED_SETTLED = 243;

    /**
     * List of acceptable decisions. May be configured value
     *
     * @var array
     */
    private static $acceptableDecisions = [
        'ACCEPT',
        'REVIEW'
    ];

    /**
     * List of acceptable reason codes. May be configured value
     *
     * @var array
     */
    private static $acceptableReasonCodes = [
        self::REASON_SUCCESSFUL,
        self::REASON_AUTH_REVERSED,
        self::REASON_TRANSACTION_REVERSED_SETTLED
    ];

    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        if (!isset($response[static::DECISION])) {
            return $this->createResult(false, [__('Your payment has been declined. Please try again.')]);
        }

        $reasonCode = $response[static::REASON_CODE] ?? $response['reasonCode'];

        $result = $this->createResult(
            in_array(
                $response[static::DECISION],
                self::$acceptableDecisions
            ) ||
            in_array(
                $reasonCode,
                self::$acceptableReasonCodes
            ),
            [__('Your payment has been declined. Please try again.')]
        );

        return $result;
    }
}
