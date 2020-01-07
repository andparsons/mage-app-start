<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;

/**
 * Validator for negotiable quote status if it is available for checkout.
 */
class CheckoutStatus implements ValidatorInterface
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
        if (!$this->restriction->canProceedToCheckout()) {
            $result->addMessage(
                __(
                    "The quote %quoteId is currently locked, and you cannot place an order from it at the moment.",
                    ['quoteId' => $quote->getId()]
                )
            );
            return $result;
        }

        return $result;
    }
}
