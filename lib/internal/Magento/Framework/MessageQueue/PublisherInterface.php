<?php
namespace Magento\Framework\MessageQueue;

/**
 * Producer to publish messages via a specific transport to a specific queue or exchange.
 *
 * @api
 * @since 102.0.3
 * @since 100.0.2
 */
interface PublisherInterface
{
    /**
     * Publishes a message to a specific queue or exchange.
     *
     * @param string $topicName
     * @param array|object $data
     * @return null|mixed
     * @throws \InvalidArgumentException If message is not formed properly
     * @since 102.0.3
     */
    public function publish($topicName, $data);
}
