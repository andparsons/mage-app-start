<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Interface for managing quotes.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteManagementInterface
{
    /**
     * Update quote status to close.
     *
     * @param int $quoteId
     * @param bool $force
     * @return bool
     */
    public function close($quoteId, $force = false);

    /**
     * Actualize quote status when merchant opens it first time.
     *
     * @param int $quoteId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function openByMerchant($quoteId);

    /**
     * Send quote from buyer to merchant.
     *
     * @param int $quoteId
     * @param string $comment
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface[] $files
     * @return bool
     */
    public function send($quoteId, $comment = '', array $files = []);

    /**
     * Submit the B2B quote to the customer.
     * The quote status for the customer will be changed to 'Updated', and the customer can work with the quote.
     *
     * @param int $quoteId
     * @param string $comment
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface[] $files
     * @return bool
     */
    public function adminSend($quoteId, $comment = '', array $files = []);

    /**
     * Update quote status to processing by customer.
     *
     * @param int $quoteId
     * @param bool $needSave
     * @return string
     * @throws NoSuchEntityException
     */
    public function updateProcessingByCustomerQuoteStatus($quoteId, $needSave = true);

    /**
     * Save draft negotiable quote.
     *
     * @param int $quoteId
     * @param array $quoteData
     * @param array $commentData
     * @return bool
     * @throws NoSuchEntityException
     */
    public function saveAsDraft($quoteId, array $quoteData, array $commentData = []);

    /**
     * Create a B2B quote based on a regular Magento quote.
     * If the B2B quote requires a shipping address (for negotiation or tax calculations),
     * add it to the regular quote before you create a B2B quote.
     *
     * @param int $quoteId
     * @param string $quoteName
     * @param string $comment
     * @param \Magento\NegotiableQuote\Api\Data\AttachmentContentInterface[] $files
     * @return bool
     */
    public function create($quoteId, $quoteName, $comment = '', array $files = []);

    /**
     * Decline the B2B quote. All custom pricing will be removed from this quote.
     * The buyer will be able to place an order using their standard catalog prices and discounts.
     *
     * @param int $quoteId
     * @param string $reason
     * @return bool
     */
    public function decline($quoteId, $reason);

    /**
     * Update quote status to ordered.
     *
     * @param int $quoteId
     * @return $this
     * @throws NoSuchEntityException
     */
    public function order($quoteId);

    /**
     * Initialize quote model instance.
     *
     * @param int $quoteId
     * @return CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNegotiableQuote($quoteId);

    /**
     * Remove item form negotiable quote.
     *
     * @param int $quoteId
     * @param int $itemId
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function removeQuoteItem($quoteId, $itemId);

    /**
     * Set has changes flag to negotiable quote.
     *
     * @param CartInterface $quote
     * @return void
     */
    public function setHasChangesInNegotiableQuote(CartInterface $quote);

    /**
     * Gets snapshot version of the quote.
     *
     * @param int $quoteId
     * @return CartInterface
     */
    public function getSnapshotQuote($quoteId);

    /**
     * Remove negotiation data from quote.
     *
     * @param int $quoteId
     * @return void
     */
    public function removeNegotiation($quoteId);

    /**
     * Recalculate quote and log changes.
     *
     * @param int $quoteId
     * @param bool $updatePrice
     * @return void
     */
    public function recalculateQuote($quoteId, $updatePrice = true);

    /**
     * Update quote items.
     *
     * @param int $quoteId
     * @param array $cartData
     * @return void
     */
    public function updateQuoteItems($quoteId, array $cartData = []);
}
