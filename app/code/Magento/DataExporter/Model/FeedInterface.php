<?php
declare(strict_types=1);

namespace Magento\DataExporter\Model;

/**
 * Interface FeedInterface
 */
interface FeedInterface
{
    /**
     * Get feed from given timestamp
     *
     * @param string $timestamp
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getFeedSince(string $timestamp): array;
}
