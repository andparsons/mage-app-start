<?php
namespace Magento\Reward\Observer\PlaceOrder;

/**
 * Interface \Magento\Reward\Observer\PlaceOrder\RestrictionInterface
 *
 */
interface RestrictionInterface
{
    /**
     * Check if reward points operations is allowed
     *
     * @return bool
     */
    public function isAllowed();
}
