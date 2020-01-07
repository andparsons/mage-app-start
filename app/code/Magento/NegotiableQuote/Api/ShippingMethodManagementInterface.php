<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface ShippingMethodManagementInterface
 * @api
 * @since 100.0.0
 */
interface ShippingMethodManagementInterface
{
    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param int $addressId The estimate address id
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddressId($cartId, $addressId);
}
