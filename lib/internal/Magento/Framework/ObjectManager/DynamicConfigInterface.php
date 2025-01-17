<?php
namespace Magento\Framework\ObjectManager;

/**
 * Interface \Magento\Framework\ObjectManager\DynamicConfigInterface
 *
 */
interface DynamicConfigInterface
{
    /**
     * Map application initialization params to Object Manager configuration format
     *
     * @return array
     */
    public function getConfiguration();
}
