<?php

namespace Magento\NegotiableQuote\Model\Validator;

/**
 * Transfer object for validation messages.
 */
class ValidatorResult
{
    /**
     * @var \Magento\Framework\Phrase[]
     */
    private $messages = [];

    /**
     * Add message to message list.
     *
     * @param \Magento\Framework\Phrase $message
     * @return void
     */
    public function addMessage(\Magento\Framework\Phrase $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Return true if count of messages is more than 0 and false then messages isn't exist.
     *
     * @return bool
     */
    public function hasMessages()
    {
        return count($this->messages) > 0;
    }

    /**
     * Retrieve validation messages.
     *
     * @return \Magento\Framework\Phrase[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
