<?php
namespace Magento\Framework\Bulk;

/**
 * Interface BulkSummaryInterface
 * @api
 * @since 102.0.3
 */
interface BulkSummaryInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BULK_ID = 'uuid';
    const DESCRIPTION = 'description';
    const START_TIME = 'start_time';
    const USER_ID = 'user_id';
    const OPERATION_COUNT = 'operation_count';
    /**#@-*/

    /**#@+
     * Bulk statuses constants
     */
    const NOT_STARTED = 0;
    const IN_PROGRESS = 1;
    const FINISHED_SUCCESSFULLY = 2;
    const FINISHED_WITH_FAILURE = 3;
    /**#@-*/

    /**
     * Get bulk uuid
     *
     * @return string
     * @since 102.0.3
     */
    public function getBulkId();

    /**
     * Set bulk uuid
     *
     * @param string $bulkUuid
     * @return $this
     * @since 102.0.3
     */
    public function setBulkId($bulkUuid);

    /**
     * Get bulk description
     *
     * @return string
     * @since 102.0.3
     */
    public function getDescription();

    /**
     * Set bulk description
     *
     * @param string $description
     * @return $this
     * @since 102.0.3
     */
    public function setDescription($description);

    /**
     * Get bulk scheduled time
     *
     * @return string
     * @since 102.0.3
     */
    public function getStartTime();

    /**
     * Set bulk scheduled time
     *
     * @param string $timestamp
     * @return $this
     * @since 102.0.3
     */
    public function setStartTime($timestamp);

    /**
     * Get user id
     *
     * @return int
     * @since 102.0.3
     */
    public function getUserId();

    /**
     * Set user id
     *
     * @param int $userId
     * @return $this
     * @since 102.0.3
     */
    public function setUserId($userId);

    /**
     * Get total number of operations scheduled in scope of this bulk
     *
     * @return int
     * @since 102.0.3
     */
    public function getOperationCount();

    /**
     * Set total number of operations scheduled in scope of this bulk
     *
     * @param int $operationCount
     * @return $this
     * @since 102.0.3
     */
    public function setOperationCount($operationCount);
}
