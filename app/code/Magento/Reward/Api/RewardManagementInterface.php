<?php
namespace Magento\Reward\Api;

/**
 * Interface RewardManagementInterface
 * @api
 * @since 100.0.2
 */
interface RewardManagementInterface
{
    /**
     * Set reward points to quote
     *
     * @param int $cartId
     * @return boolean
     */
    public function set($cartId);
}
