<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface;
use Magento\NegotiableQuote\Model\NegotiableQuoteConverter;

/**
 * Class for managing quotes currency.
 */
class Currency
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteItemManagementInterface
     */
    private $quoteItemManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteConverter
     */
    private $negotiableQuoteConverter;

    /**
     * Constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param NegotiableQuoteItemManagementInterface $quoteItemManagement
     * @param NegotiableQuoteConverter $negotiableQuoteConverter
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        NegotiableQuoteItemManagementInterface $quoteItemManagement,
        NegotiableQuoteConverter $negotiableQuoteConverter
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemManagement = $quoteItemManagement;
        $this->negotiableQuoteConverter = $negotiableQuoteConverter;
    }

    /**
     * Update currency codes and rates in quote.
     *
     * @param int $quoteId
     * @return void
     */
    public function updateQuoteCurrency($quoteId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get($quoteId, ['*']);
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $blockedStatuses = [NegotiableQuoteInterface::STATUS_CLOSED, NegotiableQuoteInterface::STATUS_ORDERED];
        if (!in_array($negotiableQuote->getStatus(), $blockedStatuses) && $this->isCurrencyDifferent($quote)) {
            $isDifferent = $this->isQuoteDifferentFromSnapshot($quote);
            $this->quoteRepository->save($quote);
            $this->quoteItemManagement->updateQuoteItemsCustomPrices($quoteId, false);
            $quote = $this->quoteRepository->get($quoteId);
            $this->updateSnapshotCurrency($quote, $isDifferent);
            $this->quoteRepository->save($quote);
        }
    }

    /**
     * Check if currencies in quote and store are different.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    private function isCurrencyDifferent(\Magento\Quote\Model\Quote $quote)
    {
        $quoteCurrency = $quote->getCurrency();
        $baseCurrency = $quote->getStore()->getBaseCurrency();
        $currentCurrency = $quote->getStore()->getCurrentCurrency();

        return $quoteCurrency->getQuoteCurrencyCode() != $currentCurrency->getCurrencyCode()
            || $quoteCurrency->getBaseCurrencyCode() != $baseCurrency->getCurrencyCode()
            || $quoteCurrency->getBaseToQuoteRate() != $baseCurrency->getRate($currentCurrency);
    }

    /**
     * Update quote snapshot.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $isDifferent
     * @return void
     */
    private function updateSnapshotCurrency(\Magento\Quote\Model\Quote $quote, $isDifferent)
    {
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        if (!$isDifferent) {
            $negotiableQuote->setSnapshot(json_encode($this->negotiableQuoteConverter->quoteToArray($quote)));
        } else {
            $snapshot = json_decode($negotiableQuote->getSnapshot(), true);
            if (!empty($snapshot['quote'])) {
                $snapshot['quote']['base_currency_code'] = $quote->getCurrency()->getBaseCurrencyCode();
                $snapshot['quote']['quote_currency_code'] = $quote->getCurrency()->getQuoteCurrencyCode();
                $snapshot['quote']['base_to_quote_rate'] = $quote->getCurrency()->getBaseToQuoteRate();
                $fieldForUpdate = [
                    'quote' => [
                        'base_subtotal_with_discount' => 'subtotal_with_discount',
                        'base_grand_total' => 'grand_total',
                    ],
                    'shipping_address' => [
                        'base_shipping_amount' => 'shipping_amount',
                        'base_tax_amount' => 'tax_amount',
                        'base_shipping_tax_amount' => 'shipping_tax_amount',
                    ],
                    'billing_address' => [
                        'base_shipping_amount' => 'shipping_amount',
                        'base_tax_amount' => 'tax_amount',
                        'base_shipping_tax_amount' => 'shipping_tax_amount',
                    ],
                ];
                $snapshot = $this->updateSnapshotPrices(
                    $snapshot,
                    $fieldForUpdate,
                    $quote->getCurrency()->getBaseToQuoteRate()
                );
            }
            $negotiableQuote->setSnapshot(json_encode($snapshot));
        }
    }

    /**
     * Compare quote and snapshot fields.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    private function isQuoteDifferentFromSnapshot(\Magento\Quote\Model\Quote $quote)
    {
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $snapshotArray = json_decode($negotiableQuote->getSnapshot(), true);
        if (empty($snapshotArray['quote'])) {
            return false;
        }
        $quoteArray = $this->negotiableQuoteConverter->quoteToArray($quote);
        $checkFields = [
            'items_count',
            'items_qty',
            'base_grand_total',
            'base_subtotal',
            'base_subtotal_with_discount',
        ];
        foreach ($checkFields as $field) {
            if ($snapshotArray['quote'][$field] != $quoteArray['quote'][$field]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Update prices in snapshot array.
     *
     * @param array $snapshot
     * @param array $fields
     * @param float $rate
     * @return array
     */
    private function updateSnapshotPrices(array $snapshot, array $fields, $rate)
    {
        foreach ($fields as $field => $value) {
            if (!isset($snapshot[$field])) {
                continue;
            }
            if (is_array($value)) {
                $snapshot[$field] = $this->updateSnapshotPrices($snapshot[$field], $value, $rate);
            } else {
                $snapshot[$value] = round($snapshot[$field] * $rate, 2);
            }
        }
        return $snapshot;
    }
}
