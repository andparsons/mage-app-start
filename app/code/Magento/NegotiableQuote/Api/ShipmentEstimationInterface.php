<?php

namespace Magento\NegotiableQuote\Api;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Interface ShipmentManagementInterface
 * @api
 * @since 100.0.0
 */
interface ShipmentEstimationInterface
{
    /**
     * Estimate shipping by address and return list of available shipping methods
     * @param mixed $cartId
     * @param AddressInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address);
}
