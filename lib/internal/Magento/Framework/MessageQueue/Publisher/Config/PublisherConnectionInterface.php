<?php
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Representation of publisher connection configuration.
 */
interface PublisherConnectionInterface
{
    /**
     * Get Connection name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get exchange name.
     *
     * @return string
     */
    public function getExchange();

    /**
     * Check if connection disabled.
     *
     * @return bool
     */
    public function isDisabled();
}
