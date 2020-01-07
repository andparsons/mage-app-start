<?php
namespace Magento\Framework\Message;

/**
 * Notice message model
 */
class Notice extends AbstractMessage
{
    /**
     * Getter message type
     *
     * @return string
     */
    public function getType()
    {
        return MessageInterface::TYPE_NOTICE;
    }
}
