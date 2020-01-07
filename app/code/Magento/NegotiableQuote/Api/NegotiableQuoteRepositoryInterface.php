<?php

namespace Magento\NegotiableQuote\Api;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface NegotiableQuoteRepositoryInterface
 * @api
 * @since 100.0.0
 */
interface NegotiableQuoteRepositoryInterface
{
    /**
     * Return the negotiable quote for a specified quote ID.
     *
     * @param int $quoteId Negotiable Quote ID.
     * @return NegotiableQuoteInterface Negotiable quote.
     */
    public function getById($quoteId);

    /**
     * Set the negotiable quote for a quote.
     *
     * @param NegotiableQuoteInterface $negotiableQuote Negotiable quote.
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function save(NegotiableQuoteInterface $negotiableQuote);

    /**
     * Get list of quotes
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param bool $snapshots
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria, $snapshots = false);

    /**
     * Get list of quotes by customer id
     *
     * @param int $customerId
     * @return \Magento\Framework\DataObject[]
     */
    public function getListByCustomerId($customerId);

    /**
     * Delete a negotiable quote. The regular quote is not affected.
     *
     * @param NegotiableQuoteInterface $quote
     * @return void
     * @throw \Magento\Framework\Exception\StateException
     */
    public function delete(NegotiableQuoteInterface $quote);
}
