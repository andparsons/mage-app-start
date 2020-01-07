<?php
namespace Magento\Eway\Gateway\Validator\Direct;

use Magento\Eway\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class ResponseValidator
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class ResponseValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionType($response)
            && $this->validateTransactionStatus($response)
            && $this->validateTransactionId($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response)
            && $this->validateAuthorisationCode($response)
            && $this->validateCardDetails($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Validates card details.
     *
     * @param array $response
     * @return bool
     */
    private function validateCardDetails(array $response)
    {
        return !empty($response[self::CUSTOMER][self::CARD_DETAILS]);
    }
}
