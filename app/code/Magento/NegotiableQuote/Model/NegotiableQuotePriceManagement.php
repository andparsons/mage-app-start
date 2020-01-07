<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model;

use Magento\NegotiableQuote\Api\NegotiableQuotePriceManagementInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Model\Validator\ValidatorInterfaceFactory;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Model\Quote\History as QuoteHistory;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

/**
 * Class for updating quote prices in case price changes occur in system.
 */
class NegotiableQuotePriceManagement implements NegotiableQuotePriceManagementInterface
{
    /**
     * @var NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var ValidatorInterfaceFactory
     */
    private $validatorFactory;

    /**
     * @var NegotiableQuoteItemManagementInterface
     */
    private $negotiableQuotItemManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RestrictionInterface
     */
    private $restriction;

    /**
     * @var QuoteHistory
     */
    private $quoteHistory;

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param ValidatorInterfaceFactory $validatorFactory
     * @param NegotiableQuoteItemManagementInterface $negotiableQuotItemManagement
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $restriction
     * @param QuoteHistory $quoteHistory
     * @param CollectionFactory $quoteCollectionFactory
     */
    public function __construct(
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        ValidatorInterfaceFactory $validatorFactory,
        NegotiableQuoteItemManagementInterface $negotiableQuotItemManagement,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $restriction,
        QuoteHistory $quoteHistory,
        CollectionFactory $quoteCollectionFactory
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->validatorFactory = $validatorFactory;
        $this->negotiableQuotItemManagement = $negotiableQuotItemManagement;
        $this->quoteRepository = $quoteRepository;
        $this->restriction = $restriction;
        $this->quoteHistory = $quoteHistory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function pricesUpdated(array $quoteIds)
    {
        $negotiableQuotes = $this->retrieveNegotiableQuotes($quoteIds);
        $oldQuoteData = $this->retrieveOldQuoteData($quoteIds);
        if (!empty($negotiableQuotes)) {
            foreach ($negotiableQuotes as $negotiableQuote) {
                $negotiableQuote->setStatus(NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN);
                $quote = $this->quoteRepository->get($negotiableQuote->getId());
                $this->negotiableQuotItemManagement->recalculateOriginalPriceTax(
                    $negotiableQuote->getQuoteId(),
                    true,
                    true
                );
                $this->quoteHistory->updateStatusLog($negotiableQuote->getId(), true);
                $this->quoteHistory->checkPricesAndDiscounts($quote, $oldQuoteData[$negotiableQuote->getId()]);
                $this->negotiableQuoteRepository->save($negotiableQuote);
            }
        }

        return true;
    }

    /**
     * Validate quotes and return array of valid negotiable quotes in case no exception occurs.
     * Will throw exception in case at least one quote doesn't pass validation.
     *
     * @param array $quoteIds
     * @return array
     * @throws InputException
     */
    private function retrieveNegotiableQuotes(array $quoteIds)
    {
        $messages = [];
        $negotiableQuotes = [];
        $validator = $this->validatorFactory->create(['action' => 'edit']);
        foreach ($quoteIds as $quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId);
                $this->restriction->setQuote($quote);
                $validateResult = $validator->validate(['quote' => $quote]);
                if ($validateResult->hasMessages()) {
                    foreach ($validateResult->getMessages() as $message) {
                        $messages[] = $message;
                    }
                } else {
                    $negotiableQuotes[] = $quote->getExtensionAttributes()->getNegotiableQuote();
                }
            } catch (NoSuchEntityException $e) {
                $messages[] = __(
                    'Requested quote is not found. Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'QuoteID', 'fieldValue' => $quoteId]
                );
            }
        }
        if (!empty($messages)) {
            $exception = new InputException(
                __('Cannot obtain the requested data. You must fix the errors listed below first.')
            );
            foreach ($messages as $message) {
                $exception->addError($message);
            }
            throw $exception;
        }

        return $negotiableQuotes;
    }

    /**
     * Retrieve quotes data before quotes get updated for writing price changes into the history log.
     *
     * @param array $quoteIds
     * @return array
     */
    private function retrieveOldQuoteData(array $quoteIds)
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter('entity_id', ['in' => $quoteIds]);
        $oldData = [];
        foreach ($quoteCollection->getItems() as $quote) {
            $oldData[$quote->getId()] = $this->quoteHistory->collectOldDataFromQuote($quote);
        }

        return $oldData;
    }
}
