<?php
namespace Magento\Braintree\Gateway\Validator;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class GeneralResponseValidator extends AbstractValidator
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var ErrorCodeProvider
     */
    private $errorCodeProvider;

    /**
     * Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param ErrorCodeProvider $errorCodeProvider
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        ErrorCodeProvider $errorCodeProvider
    ) {
        parent::__construct($resultFactory);
        $this->subjectReader = $subjectReader;
        $this->errorCodeProvider = $errorCodeProvider;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        /** @var Successful|Error $response */
        $response = $this->subjectReader->readResponseObject($validationSubject);

        $isValid = true;
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
            }
        }
        $errorCodes = $this->errorCodeProvider->getErrorCodes($response);

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }

    /**
     * @return array
     */
    protected function getResponseValidators()
    {
        return [
            function ($response) {
                return [
                    property_exists($response, 'success') && $response->success === true,
                    [$response->message ?? __('Braintree error response.')]
                ];
            }
        ];
    }
}
