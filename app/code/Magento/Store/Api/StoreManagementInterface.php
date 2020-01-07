<?php
namespace Magento\Store\Api;

/**
 * @api
 * @since 100.0.2
 */
interface StoreManagementInterface
{
    /**
     * Provide the number of store count
     *
     * @return int
     */
    public function getCount();
}
