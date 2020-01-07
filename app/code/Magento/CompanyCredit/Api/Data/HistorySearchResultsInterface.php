<?php
namespace Magento\CompanyCredit\Api\Data;

/**
 * Interface for History search results.
 *
 * @api
 * @since 100.0.0
 */
interface HistorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get History list.
     *
     * @return \Magento\CompanyCredit\Api\Data\HistoryDataInterface[]
     */
    public function getItems();

    /**
     * Set History list.
     *
     * @param \Magento\CompanyCredit\Api\Data\HistoryDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
