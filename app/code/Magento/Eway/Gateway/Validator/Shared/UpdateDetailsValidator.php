<?php
namespace Magento\Eway\Gateway\Validator\Shared;

use Magento\Eway\Gateway\Helper;
use Magento\Eway\Gateway\Validator\AbstractResponseValidator;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class UpdateDetailsValidator
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class UpdateDetailsValidator extends AbstractResponseValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);
        $amount = SubjectReader::readAmount($validationSubject);
        $transactionId = Helper\SubjectReader::readTransactionId($validationSubject);

        $errorMessages = [];
        $validationResult = $this->validateErrors($response)
            && $this->validateTotalAmount($response, $amount)
            && $this->validateTransactionStatus($response)
            && $this->validateResponseCode($response)
            && $this->validateResponseMessage($response)
            && $this->validateAuthorisationCode($response)
            && $this->validateTransactionId($response)
            && $this->validateTransactionConsistency($response, $transactionId);

        if (!$validationResult) {
            $errorMessages = [__('Transaction has been declined. Please try again later.')];
        }

        return $this->createResult($validationResult, $errorMessages);
    }

    /**
     * Validates total amount.
     *
     * @param array $response
     * @param array|number|string $amount
     * @return bool
     */
    protected function validateTotalAmount(array $response, $amount)
    {
        return isset($response[self::TOTAL_AMOUNT])
            && (string)($response[self::TOTAL_AMOUNT] / 100) === (string)$amount;
    }

    /**
     * Validates transaction consistency.
     *
     * @param array $response
     * @param string $transactionId
     * @return bool
     */
    private function validateTransactionConsistency(array $response, $transactionId)
    {
        return (string)$response[self::TRANSACTION_ID] === (string)$transactionId;
    }
}
