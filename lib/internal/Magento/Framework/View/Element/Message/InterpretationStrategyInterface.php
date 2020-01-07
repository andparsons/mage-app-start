<?php
namespace Magento\Framework\View\Element\Message;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\InterpretationStrategyInterface
 *
 */
interface InterpretationStrategyInterface
{
    /**
     * Interpret message
     *
     * @param MessageInterface $message
     * @return string
     */
    public function interpret(MessageInterface $message);
}
