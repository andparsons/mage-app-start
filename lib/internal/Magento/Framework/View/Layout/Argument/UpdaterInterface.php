<?php
namespace Magento\Framework\View\Layout\Argument;

/**
 * Interface of value modification with no value loss
 */
interface UpdaterInterface
{
    /**
     * Return modified version of an input value
     *
     * @param mixed $value
     * @return mixed
     */
    public function update($value);
}
