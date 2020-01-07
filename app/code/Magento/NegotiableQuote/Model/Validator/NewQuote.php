<?php

namespace Magento\NegotiableQuote\Model\Validator;

/**
 * Validate to check if negotiable quote doesn't exist.
 */
class NewQuote implements ValidatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->validatorResultFactory = $validatorResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $result = $this->validatorResultFactory->create();
        if (empty($data['quote'])) {
            return $result;
        }
        $quote = $data['quote'];
        if ($quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
        ) {
            $result->addMessage(
                __(
                    'Invalid attribute %fieldName: A B2B quote for this quoteID already exists. '
                    . 'Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'quoteId', 'fieldValue' => $quote->getId()]
                )
            );
        }

        return $result;
    }
}
