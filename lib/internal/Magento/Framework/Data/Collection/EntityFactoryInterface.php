<?php
namespace Magento\Framework\Data\Collection;

/**
 * Interface \Magento\Framework\Data\Collection\EntityFactoryInterface
 *
 */
interface EntityFactoryInterface
{
    /**
     * Create new object instance
     *
     * @param string $type
     * @param array $arguments
     * @return mixed
     */
    public function create($type, array $arguments = []);
}
