<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory;

/**
 * Prepare validation result for all validators described in the config.
 */
class Validator implements ValidatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterface[]
     */
    private $validators;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @var array
     */
    private $validateConfig = [];

    /**
     * @var string
     */
    private $action;

    /**
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorInterface[] $validators
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     * @param array $validateConfig
     * @param string $action
     */
    public function __construct(
        array $validators,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory,
        array $validateConfig,
        $action
    ) {
        $this->validators = $validators;
        $this->validatorResultFactory = $validatorResultFactory;
        $this->validateConfig = $validateConfig;
        $this->action = $action;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $result = $this->validatorResultFactory->create();
        if (!empty($this->validateConfig[$this->action])) {
            foreach ($this->validateConfig[$this->action] as $validatorIndex) {
                $validationResult = $this->validators[$validatorIndex]->validate($data);
                if ($validationResult->hasMessages()) {
                    return $validationResult;
                }
            }
        } elseif (empty($this->action)) {
            foreach ($this->validators as $validator) {
                $validationResult = $validator->validate($data);
                if ($validationResult->hasMessages()) {
                    return $validationResult;
                }
            }
        }

        return $result;
    }
}
