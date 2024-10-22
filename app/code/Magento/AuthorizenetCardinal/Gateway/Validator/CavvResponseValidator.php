<?php

declare(strict_types=1);

namespace Magento\AuthorizenetCardinal\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetCardinal\Model\Config;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validates cardholder authentication verification response code.
 */
class CavvResponseValidator extends AbstractValidator
{
    /**
     * The result code that authorize.net returns if CAVV passed validation.
     */
    private const RESULT_CODE_SUCCESS = '2';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        Config $config
    ) {
        parent::__construct($resultFactory);

        $this->resultFactory = $resultFactory;
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        if ($this->config->isActive() === false) {
            return $this->createResult(true);
        }

        $response = $this->subjectReader->readResponse($validationSubject);
        $transactionResponse = $response['transactionResponse'];

        $cavvResultCode = $transactionResponse['cavvResultCode'] ?? '';
        $isValid = $cavvResultCode === self::RESULT_CODE_SUCCESS;
        $errorCodes = [];
        $errorMessages = [];

        if (!$isValid) {
            $errorCodes[] = $transactionResponse['cavvResultCode'];
            $errorMessages[] = 'CAVV failed validation';
        }

        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }
}
