<?php

namespace Magento\NegotiableQuote\Api;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;

/**
 * Interface NegotiableQuoteItemRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteItemRepositoryInterface
{

    /**
     * Set negotiable quote item for quote item
     *
     * @param NegotiableQuoteItemInterface $quoteItem
     * @return bool
     */
    public function save(NegotiableQuoteItemInterface $quoteItem);
}
