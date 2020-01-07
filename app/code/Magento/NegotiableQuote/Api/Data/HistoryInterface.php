<?php

namespace Magento\NegotiableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface HistoryInterface
 * @api
 * @since 100.0.0
 */
interface HistoryInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array.
     */
    const HISTORY_ID = 'history_id';
    const QUOTE_ID = 'quote_id';
    const IS_SELLER = 'is_seller';
    const AUTHOR_ID = 'author_id';
    const IS_DRAFT = 'is_draft';
    const STATUS = 'status';
    const LOG_DATA = 'log_data';
    const SNAPSHOT_DATA = 'snapshot_data';
    const CREATED_AT = 'created_at';
    /**#@-*/

    /**#@+
     * Statuses for history log.
     */
    const STATUS_CREATED = 'created';
    const STATUS_UPDATED = 'updated';
    const STATUS_CLOSED = 'closed';
    const STATUS_UPDATED_BY_SYSTEM = 'updated_by_system';
    /**#@-*/

    /**
     * Get history log ID.
     *
     * @return int
     */
    public function getHistoryId();

    /**
     * Set history log ID.
     *
     * @param int $id
     * @return $this
     */
    public function setHistoryId($id);

    /**
     * Get negotiable quote ID, that this log belongs to.
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set negotiable quote ID, that this log belongs to.
     *
     * @param int $id
     * @return $this
     */
    public function setQuoteId($id);

    /**
     * Returns true if changed by the seller.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSeller();

    /**
     * Set is seller changed the quote.
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsSeller($flag);

    /**
     * Get changes author ID.
     *
     * @return int
     */
    public function getAuthorId();

    /**
     * Set changes author.
     *
     * @param int $authorId
     * @return $this
     */
    public function setAuthorId($authorId);

    /**
     * Get log status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set log status.
     *
     * @param string $logStatus
     * @return $this
     */
    public function setStatus($logStatus);

    /**
     * Get serialized log data.
     *
     * @return string
     */
    public function getLogData();

    /**
     * Set serialized log data.
     *
     * @param string $logData
     * @return $this
     */
    public function setLogData($logData);

    /**
     * Get serialized quote snapshot data.
     *
     * @return string
     */
    public function getSnapshotData();

    /**
     * Set serialized quote snapshot data.
     *
     * @param string $snapshotData
     * @return $this
     */
    public function setSnapshotData($snapshotData);

    /**
     * Get log creation timestamp.
     *
     * @return int
     */
    public function getCreatedAt();

    /**
     * Set log creation timestamp.
     *
     * @param int $timestamp
     * @return $this
     */
    public function setCreatedAt($timestamp);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\NegotiableQuote\Api\Data\HistoryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\NegotiableQuote\Api\Data\HistoryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\NegotiableQuote\Api\Data\HistoryExtensionInterface $extensionAttributes
    );
}
