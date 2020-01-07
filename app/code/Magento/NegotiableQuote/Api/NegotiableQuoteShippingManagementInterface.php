<?php

namespace Magento\NegotiableQuote\Api;

use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface for add and update negotiable quote shipping method.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteShippingManagementInterface
{
    /**
     * Updates the shipping method on a negotiable quote.
     *
     * @param int $quoteId Negotiable Quote id
     * @param string $shippingMethod The shipping method code.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The shipping method is not valid for an empty cart.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The shipping method could not be saved.
     * @throws NoSuchEntityException The Cart includes virtual product(s) only, so a shipping address is not used.
     * @throws \Magento\Framework\Exception\StateException The billing or shipping address is missing.
     */
    public function setShippingMethod($quoteId, $shippingMethod);
}
