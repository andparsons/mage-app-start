<?php
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;

/**
 * @inheritdoc
 */
class BillingAddressValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setStoreId($quote->getStoreId());
        $validationResult = $billingAddress->validate();
        if ($validationResult !== true) {
            $validationErrors = [__($this->generalMessage)];
        }
        if (is_array($validationResult)) {
            $validationErrors = array_merge($validationErrors, $validationResult);
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
