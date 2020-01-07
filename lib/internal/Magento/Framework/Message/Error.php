<?php
namespace Magento\Framework\Message;

/**
 * Error message model
 */
class Error extends AbstractMessage
{
    /**
     * Getter message type
     *
     * @return string
     */
    public function getType()
    {
        return MessageInterface::TYPE_ERROR;
    }
}
