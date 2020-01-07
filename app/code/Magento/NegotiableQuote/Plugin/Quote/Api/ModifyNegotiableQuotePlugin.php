<?php

namespace Magento\NegotiableQuote\Plugin\Quote\Api;

/**
 * Plugin validates quote item before updating/deleting it via Web API call and updates/deletes it.
 */
class ModifyNegotiableQuotePlugin
{
    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $negotiableQuoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\History
     */
    private $quoteHistory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var array
     */
    private $oldQuoteData;

    /**
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory $validatorFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement
     * @param \Magento\NegotiableQuote\Model\Quote\History $quoteHistory
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory $validatorFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface $negotiableQuoteItemManagement,
        \Magento\NegotiableQuote\Model\Quote\History $quoteHistory,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
    ) {
        $this->validatorFactory = $validatorFactory;
        $this->quoteRepository = $quoteRepository;
        $this->negotiableQuoteItemManagement = $negotiableQuoteItemManagement;
        $this->quoteHistory = $quoteHistory;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    /**
     * Plugin before deleteById.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param int $cartId The cart ID.
     * @param int $itemId The item ID of the item to be removed.
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDeleteById(\Magento\Quote\Api\CartItemRepositoryInterface $subject, $cartId, $itemId)
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $this->quoteRepository->get($cartId);
        $this->retrieveOldQuoteData($cartId);
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()) {
            $messages = $this->validateQuoteStatus($quote);
            if ($quote->getItemsCount() <= 1) {
                $messages[] = __('Cannot delete all items from a B2B quote. The quote must contain at least one item.');
            }
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
     * Plugin before save.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartItemInterface $cartItem
    ) {
        $cartId = $cartItem->getQuoteId();
        $this->retrieveOldQuoteData($cartId);
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $this->quoteRepository->get($cartId);
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()) {
            $messages = $this->validateQuoteStatus($quote);
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
     * Update negotiable quote status and history log after quote item removal.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param bool $result
     * @param int $cartId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(\Magento\Quote\Api\CartItemRepositoryInterface $subject, $result, $cartId)
    {
        $quote = $this->quoteRepository->get($cartId);
        if ($result && $quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $negotiableQuote->setStatus(
                \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN
            );
            $this->negotiableQuoteItemManagement->recalculateOriginalPriceTax(
                $negotiableQuote->getQuoteId(),
                true,
                true
            );
            $this->quoteHistory->updateStatusLog($negotiableQuote->getQuoteId(), true);
            $this->quoteHistory->checkPricesAndDiscounts($quote, $this->oldQuoteData[$cartId]);
            $this->negotiableQuoteRepository->save($negotiableQuote);
        }

        return $result;
    }

    /**
     * Update quote status and history log after item update.
     *
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartItemInterface $result
     * @param \Magento\Quote\Api\Data\CartItemInterface $cartItem
     * @return \Magento\Quote\Api\Data\CartItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Quote\Api\CartItemRepositoryInterface $subject,
        \Magento\Quote\Api\Data\CartItemInterface $result,
        \Magento\Quote\Api\Data\CartItemInterface $cartItem
    ) {
        $quote = $this->quoteRepository->get($cartItem->getQuoteId());
        if ($quote->getExtensionAttributes() && $quote->getExtensionAttributes()->getNegotiableQuote()
            && $quote->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()) {
            $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
            $negotiableQuote->setStatus(
                \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN
            );
            $this->negotiableQuoteItemManagement->recalculateOriginalPriceTax(
                $negotiableQuote->getQuoteId(),
                true,
                true
            );
            $this->quoteHistory->updateStatusLog($negotiableQuote->getQuoteId(), true);
            $this->quoteHistory->checkPricesAndDiscounts($quote, $this->oldQuoteData[$quote->getId()]);
            $this->negotiableQuoteRepository->save($negotiableQuote);
        }
        return $result;
    }

    /**
     * Validate if quote is in "created", "processing by admin" or "submitted by customer" status.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return array
     */
    private function validateQuoteStatus(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $messages = [];
        $validator = $this->validatorFactory->create(['action' => 'edit']);
        $validateResult = $validator->validate(['quote' => $quote]);
        if ($validateResult->hasMessages()) {
            foreach ($validateResult->getMessages() as $message) {
                $messages[] = $message;
            }
        }
        return $messages;
    }

    /**
     * Retrieve quote data before quote gets updated for writing price changes into the history log.
     *
     * @param int $quoteId
     * @return void
     */
    private function retrieveOldQuoteData($quoteId)
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $this->oldQuoteData[$quoteId] = $this->quoteHistory->collectOldDataFromQuote(
            $quoteCollection->addFieldToFilter('entity_id', $quoteId)->getFirstItem()
        );
    }
}
