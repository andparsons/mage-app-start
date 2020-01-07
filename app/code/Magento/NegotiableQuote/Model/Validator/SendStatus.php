<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;

/**
 * Validator for negotiable quote status if it is available for send.
 */
class SendStatus implements ValidatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @param RestrictionInterface $restriction
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        RestrictionInterface $restriction,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->restriction = $restriction;
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
        if (!$this->restriction->canSubmit()) {
            $result->addMessage(
                __(
                    "The quote %quoteId is currently locked and cannot be submitted to the customer. "
                    . "Please check the quote status.",
                    ['quoteId' => $quote->getId()]
                )
            );
            return $result;
        }

        return $result;
    }
}
