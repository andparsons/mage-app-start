<?php
namespace Magento\Framework\Message;

/**
 * Warning message model
 */
class Warning extends AbstractMessage
{
    /**
     * Getter message type
     *
     * @return string
     */
    public function getType()
    {
        return MessageInterface::TYPE_WARNING;
    }
}
