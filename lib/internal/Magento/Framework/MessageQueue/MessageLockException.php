<?php
namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class MessageLockException to be thrown when a message being processed is already in the lock table.
 *
 * @api
 * @since 102.0.3
 */
class MessageLockException extends LocalizedException
{

}
