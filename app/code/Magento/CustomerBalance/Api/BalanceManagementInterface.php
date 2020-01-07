<?php

namespace Magento\CustomerBalance\Api;

/**
 * Customer balance(store credit) operations
 * @api
 * @since 100.0.2
 */
interface BalanceManagementInterface
{
    /**
     * Apply store credit
     *
     * @param int $cartId
     * @return bool
     */
    public function apply($cartId);
}
