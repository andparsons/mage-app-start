<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface HistoryManagementInterface
 * @api
 * @since 100.0.0
 */
interface HistoryManagementInterface
{
    /**
     * Add history log with status "created" and snapshot data of the new quote
     *
     * @param int $quoteId
     * @return void
     */
    public function createLog($quoteId);

    /**
     * Add history log with status "updated" and snapshot quote data
     *
     * @param int $quoteId
     * @param bool $isSeller
     * @param string $status
     * @return void
     */
    public function updateLog($quoteId, $isSeller = false, $status = '');

    /**
     * Add history log about item remove from catalog
     *
     * @param int $quoteId
     * @param int $productId
     * @return void
     */
    public function addItemRemoveCatalogLog($quoteId, $productId);

    /**
     * Add history log with status "closed"
     *
     * @param int $quoteId
     * @return void
     */
    public function closeLog($quoteId);

    /**
     * Add history log only with quote status changes.
     *
     * @param int $quoteId
     * @param bool $isSeller
     * @param bool $isExpired
     * @return void
     */
    public function updateStatusLog($quoteId, $isSeller = false, $isExpired = false);

    /**
     * Add history log with custom message.
     *
     * @param int $quoteId
     * @param array $values
     * @param bool $isSeller
     * @param bool $isSystem
     * @return void
     */
    public function addCustomLog($quoteId, array $values, $isSeller = false, $isSystem = false);

    /**
     * Get list of history logs for negotiable quote by ID.
     *
     * @param int $quoteId
     * @return ExtensibleDataInterface[]
     */
    public function getQuoteHistory($quoteId);

    /**
     * Return list of updates of the quote for one log.
     *
     * @param int $logId
     * @return array
     */
    public function getLogUpdatesList($logId);

    /**
     * Update draft logs.
     *
     * @param int $logId
     * @param bool $updateLastLog
     * @return array
     */
    public function updateDraftLogs($logId, $updateLastLog = false);

    /**
     * Update system log status.
     *
     * @param int $quoteId
     * @return void
     */
    public function updateSystemLogsStatus($quoteId);
}
