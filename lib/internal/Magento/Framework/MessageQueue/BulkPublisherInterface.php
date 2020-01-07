<?php
namespace Magento\Framework\MessageQueue;

/**
 * Producer to publish messages in bulk via a specific transport to a specific queue or exchange.
 */
interface BulkPublisherInterface
{
    /**
     * Publishes messages in bulk to a specific queue or exchange.
     *
     * @param string $topicName
     * @param array|object $data
     * @return null|mixed
     */
    public function publish($topicName, $data);
}
