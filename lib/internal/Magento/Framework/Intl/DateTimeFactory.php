<?php
namespace Magento\Framework\Intl;

/**
 * Class DateTimeFactory
 * @package Magento\Framework
 */
class DateTimeFactory
{
    /**
     * Factory method for \DateTime
     *
     * @param string $time
     * @param \DateTimeZone $timezone
     * @return \DateTime
     */
    public function create($time = 'now', \DateTimeZone $timezone = null)
    {
        return new \DateTime($time, $timezone);
    }
}
