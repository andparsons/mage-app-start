<?php
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\QueueInterface
 *
 * @api
 * @since 102.0.3
 */
interface QueueFactoryInterface
{
    /**
     * Create queue instance.
     *
     * @param string $queueName
     * @param string $connectionName
     * @return QueueInterface
     * @since 102.0.3
     */
    public function create($queueName, $connectionName);
}
