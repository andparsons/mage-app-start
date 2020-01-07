<?php

namespace Magento\NegotiableQuote\Api;

/**
 * Interface for updating quote prices in case price changes occur in system.
 *
 * @api
 * @since 100.0.0
 */
interface NegotiableQuotePriceManagementInterface
{
    /**
     * Refreshes item prices, taxes, discounts, cart rules in the negotiable quote as per the latest changes in the
     * catalog / shared catalog and in the price rules. Depending on the negotiable quote state and totals,
     * all or just some of quote numbers will be recalculated. 'Update Prices' parameter forces refresh on any quote
     * that is not locked for admin user, including the quotes with a negotiated price.
     * The request can be applied to one or more quotes at the same time.
     *
     * @param int[] $quoteIds
     * @throws \Magento\Framework\Exception\InputException
     * @return bool
     */
    public function pricesUpdated(array $quoteIds);
}
