<?php
namespace Magento\Framework\Data;

/**
 * Interface ValueSourceInterface
 */
interface ValueSourceInterface
{
    /**
     * Get value by name
     *
     * @param string $name
     * @return mixed
     */
    public function getValue($name);
}
