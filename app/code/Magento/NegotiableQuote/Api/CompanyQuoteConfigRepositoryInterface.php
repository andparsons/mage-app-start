<?php

namespace Magento\NegotiableQuote\Api;

use Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface;

/**
 * Interface CompanyQuoteConfigRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface CompanyQuoteConfigRepositoryInterface
{

    /**
     * Set quote config for company
     *
     * @param CompanyQuoteConfigInterface $quoteConfig company quote config.
     * @return bool
     */
    public function save(CompanyQuoteConfigInterface $quoteConfig);
}
