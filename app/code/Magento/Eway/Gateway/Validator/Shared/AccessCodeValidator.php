<?php
namespace Magento\Eway\Gateway\Validator\Shared;

use Magento\Eway\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class AccessCodeValidator
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class AccessCodeValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateAccessCode($response);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Validates access code.
     *
     * @param array $response
     * @return bool
     */
    private function validateAccessCode(array $response)
    {
        return !empty($response[self::ACCESS_CODE]);
    }
}
