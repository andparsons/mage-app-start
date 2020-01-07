<?php
namespace Magento\Company\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for company structure search results
 *
 * @api
 * @since 100.0.0
 */
interface StructureSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get structures list
     *
     * @return \Magento\Company\Api\Data\StructureInterface[]
     */
    public function getItems();

    /**
     * Set structures list
     *
     * @param \Magento\Company\Api\Data\StructureInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
