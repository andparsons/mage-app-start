<?php
namespace Magento\Framework\MessageQueue;

/**
 * Interface for mapping between original and merged messages.
 */
interface MergedMessageInterface
{
    /**
     * Get merged message instance.
     *
     * @return mixed
     */
    public function getMergedMessage();

    /**
     * Get original messages ids connected with the merged message.
     *
     * @return array
     */
    public function getOriginalMessagesIds();
}
