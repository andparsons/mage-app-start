<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Model;

use Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory;
use Magento\Quote\Api\Data\CartExtensionFactory;

/**
 * Plugin validates quote before updating it via Web API call.
 */
class QuoteUpdateValidator
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * Quote ids that already validated at this session.
     *
     * @var array
     */
    private $validatedQuotes = [];

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface[]
     */
    private $initialQuotes = [];

    /**
     * An array of attributes that can not be modified while updating the quote.
     *
     * @var array
     */
    private $attributes = [
        'created_at',
        'customer_id',
        'store_id',
    ];

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param ValidatorInterfaceFactory $validatorFactory
     * @param CartExtensionFactory $cartExtensionFactory
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        ValidatorInterfaceFactory $validatorFactory,
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->validatorFactory = $validatorFactory;
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * Verify if quote can be updated.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Quote\Api\CartRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (!in_array($quote->getId(), $this->validatedQuotes) && $extensionAttributes
            && $extensionAttributes->getNegotiableQuote()
        ) {
            if ($quote->getId()) {
                $this->validatedQuotes[] = $quote->getId();
            }
            $messages = $this->retrieveErrorMessages($quote);
            if (!empty($messages)) {
                $exception = new \Magento\Framework\Exception\InputException();
                foreach ($messages as $message) {
                    $exception->addError($message);
                }
                throw $exception;
            }
        }
    }

    /**
     * Retrieve array of error messages from quote validations.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function retrieveErrorMessages(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $messages = $this->validateQuoteId($quote);
        if ($messages) {
            return $messages;
        }
        $initialQuote = $this->getQuote($quote->getId());
        $oldNegotiableQuote = $initialQuote->getExtensionAttributes()->getNegotiableQuote();
        if ($oldNegotiableQuote->getIsRegularQuote()) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $negotiableQuote->setQuoteId($quote->getId());
            if (!$negotiableQuote->getStatus()) {
                $negotiableQuote->setStatus($oldNegotiableQuote->getStatus());
            }
            $validator = $this->validatorFactory->create(['action' => 'edit']);
            $validateResult = $validator->validate(['quote' => $initialQuote]);
            $messages = array_merge(
                $validateResult->getMessages(),
                $this->validateQuoteAttributes($quote),
                $this->validateQuoteShipping($quote)
            );
        }

        return $messages;
    }

    /**
     * Check if quote id matches negotiable quote id.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function validateQuoteId(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $messages = [];
        if (!$quote->getId()) {
            $messages[] = __(
                '"%fieldName" is required. Enter and try again.',
                [
                    'fieldName' => \Magento\Quote\Api\Data\CartInterface::KEY_ID
                ]
            );
        } elseif ($quote->getExtensionAttributes()->getNegotiableQuote()->getQuoteId()
            && $quote->getId() != $quote->getExtensionAttributes()->getNegotiableQuote()->getQuoteId()
        ) {
            $messages[] = __(
                'You cannot update the requested attribute. Row ID: %fieldName = %fieldValue.',
                [
                    'fieldName' => \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::QUOTE_ID,
                    'fieldValue' => $quote->getExtensionAttributes()->getNegotiableQuote()->getQuoteId()
                ]
            );
        }

        return $messages;
    }

    /**
     * Check if none of the quote parameters that can't be changed are changed.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function validateQuoteAttributes(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $messages = [];
        $initialQuote = $this->getQuote($quote->getId());
        foreach ($this->attributes as $attribute) {
            $method = 'get'
                . $this->snakeCaseToUpperCamelCase($attribute);

            if ($quote->$method() && $initialQuote->$method() != $quote->$method()) {
                $messages[] = __(
                    'You cannot update the requested attribute. Row ID: %fieldName = %fieldValue.',
                    ['fieldName' => $attribute, 'fieldValue' => $quote->$method()]
                );
            }
        }

        return $messages;
    }

    /**
     * Converts an input string from snake_case to upper CamelCase.
     *
     * @param string $input
     * @return string
     */
    private function snakeCaseToUpperCamelCase($input)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $input)));
    }

    /**
     * Check if shipping method is set in case shipping price is being updated.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function validateQuoteShipping(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $messages = [];
        if ($quote->getExtensionAttributes()->getNegotiableQuote()->getShippingPrice() !== null) {
            if (!$quote->getShippingAddress() || !$quote->getShippingAddress()->getShippingMethod()) {
                $messages[] = __('Cannot set the shipping price. You must select a shipping method first.');
            }
        }

        return $messages;
    }

    /**
     * Retrieve quote before it is written to the database.
     *
     * @param int $quoteId
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote($quoteId)
    {
        if (empty($this->initialQuotes[$quoteId])) {
            $quoteCollection = $this->quoteCollectionFactory->create();
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $quoteCollection->addFieldToFilter('entity_id', $quoteId)->getFirstItem();
            $negotiableQuote = $this->negotiableQuoteRepository->getById($quoteId);
            if (!$quote->getExtensionAttributes()) {
                $quote->setExtensionAttributes($this->cartExtensionFactory->create());
            }
            $quote->getExtensionAttributes()->setNegotiableQuote($negotiableQuote);
            $this->initialQuotes[$quoteId] = $quote;
        }

        return $this->initialQuotes[$quoteId];
    }
}
