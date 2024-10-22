<?php
namespace Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

/**
 * Representation of message queue handler configuration.
 */
interface HandlerInterface
{
    /**
     * Get handler type name.
     *
     * @return string
     */
    public function getType();

    /**
     * Get handler method name.
     *
     * @return string
     */
    public function getMethod();
}
