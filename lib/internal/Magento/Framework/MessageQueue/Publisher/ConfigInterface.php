<?php
namespace Magento\Framework\MessageQueue\Publisher;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemInterface;

/**
 * Publisher config interface provides access data declared in etc/queue_publisher.xml
 *
 * @api
 * @since 102.0.3
 */
interface ConfigInterface
{
    /**
     * Get publisher configuration by topic.
     *
     * @param string $topic
     * @return PublisherConfigItemInterface
     * @throws LocalizedException
     * @throws \LogicException
     * @since 102.0.3
     */
    public function getPublisher($topic);

    /**
     * Get list of all publishers declared in the system.
     *
     * @return PublisherConfigItemInterface[]
     * @throws \LogicException
     * @since 102.0.3
     */
    public function getPublishers();
}
