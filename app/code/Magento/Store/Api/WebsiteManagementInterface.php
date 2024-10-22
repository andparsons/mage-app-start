<?php
namespace Magento\Store\Api;

/**
 * @api
 * @since 100.0.2
 */
interface WebsiteManagementInterface
{
    /**
     * Provide the number of website count
     *
     * @return int
     */
    public function getCount();
}
