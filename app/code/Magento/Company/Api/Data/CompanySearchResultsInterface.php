<?php
namespace Magento\Company\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for company search results
 *
 * @api
 * @since 100.0.0
 */
interface CompanySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get companies list
     *
     * @return \Magento\Company\Api\Data\CompanyInterface[]
     */
    public function getItems();

    /**
     * Set companies list
     *
     * @param \Magento\Company\Api\Data\CompanyInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
