<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface CartTotalRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface CartTotalRepositoryInterface
{
    /**
     * Returns quote totals data for a specified cart.
     *
     * @param int $cartId The cart ID.
     * @return \Magento\Quote\Api\Data\TotalsInterface Quote totals data.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     */
    public function get($cartId);
}
