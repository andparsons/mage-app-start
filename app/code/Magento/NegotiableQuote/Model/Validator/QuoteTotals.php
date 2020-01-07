<?php

namespace Magento\NegotiableQuote\Model\Validator;

/**
 * Validator for quote totals and items amount.
 */
class QuoteTotals implements ValidatorInterface
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @param \Magento\NegotiableQuote\Helper\Config $configHelper
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        \Magento\NegotiableQuote\Helper\Config $configHelper,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->configHelper = $configHelper;
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
        if ($quote->getItemsCount() < 1) {
            $result->addMessage(__('Cannot create a B2B quote. You must provide one or more quote items.'));
            return $result;
        }

        if (!$this->configHelper->isQuoteAllowed($quote)) {
            $message = $this->configHelper->getMinimumAmountMessage()
                ? __('Cannot create the B2B quote.' . $this->configHelper->getMinimumAmountMessage())
                : __(
                    'Cannot create the B2B quote. The minimum order for a quote request is %currency%amount',
                    [
                        "amount" => $this->configHelper->getMinimumAmount(),
                        "currency" => $quote->getCurrency()->getQuoteCurrencyCode()
                    ]
                );
            $result->addMessage($message);
            return $result;
        }

        return $result;
    }
}
