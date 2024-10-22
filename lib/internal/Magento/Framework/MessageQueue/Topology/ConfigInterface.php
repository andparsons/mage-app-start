<?php
namespace Magento\Framework\MessageQueue\Topology;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;

/**
 * Topology config interface provides access data declared in etc/queue_topology.xml
 *
 * @api
 * @since 102.0.3
 */
interface ConfigInterface
{
    /**
     * Get exchange configuration by exchange name.
     *
     * @param string $name
     * @param string $connection
     * @return ExchangeConfigItemInterface
     * @throws LocalizedException
     * @throws \LogicException
     * @since 102.0.3
     */
    public function getExchange($name, $connection);

    /**
     * Get list of all exchanges declared in the system.
     *
     * @return ExchangeConfigItemInterface[]
     * @throws \LogicException
     * @since 102.0.3
     */
    public function getExchanges();

    /**
     * Get list of all queues declared in the system.
     *
     * @return QueueConfigItemInterface[]
     * @throws \LogicException
     * @since 102.0.3
     */
    public function getQueues();
}
