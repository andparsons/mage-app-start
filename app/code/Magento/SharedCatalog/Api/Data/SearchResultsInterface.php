<?php
namespace Magento\SharedCatalog\Api\Data;

/**
 * Interface for Shared Catalog search results.
 * @api
 * @since 100.0.0
 */
interface SearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Shared Catalog list.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface[]
     */
    public function getItems();

    /**
     * Set Shared Catalog list.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
