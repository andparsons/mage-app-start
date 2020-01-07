<?php
namespace Magento\Framework\DB\Sequence;

/**
 * Interface represents sequence
 */
interface SequenceInterface
{
    /**
     * Retrieve current value
     *
     * @return string
     */
    public function getCurrentValue();

    /**
     * Retrieve next value
     *
     * @return string
     */
    public function getNextValue();
}
