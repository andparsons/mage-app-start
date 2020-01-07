<?php
namespace Magento\SharedCatalog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface as FrameworkSearchResultsInterface;

/**
 * Interface for Shared Catalog Product Item search results.
 * @api
 * @since 100.0.0
 */
interface ProductItemSearchResultsInterface extends FrameworkSearchResultsInterface
{
    /**
     * Get Shared Catalog Product Item list.
     *
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface[]
     */
    public function getItems();

    /**
     * Set Shared Catalog Product Item list.
     *
     * @param \Magento\SharedCatalog\Api\Data\ProductItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
