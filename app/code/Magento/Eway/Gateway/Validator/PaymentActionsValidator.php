<?php
namespace Magento\Eway\Gateway\Validator;

use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class PaymentActionsValidator for Cancel and Capture commands
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class PaymentActionsValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTransactionStatus($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseMessage($response)
            && $this->validateResponseCode($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Validates response code.
     *
     * @param array $response
     * @return bool
     */
    protected function validateResponseCode(array $response)
    {
        return isset($response[self::RESPONSE_CODE])
            && $response[self::RESPONSE_CODE] === $response[self::RESPONSE_MESSAGE];
    }
}
