<?php
declare(strict_types=1);

namespace Magento\CatalogInventory\Api;

/**
 * @api
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
 * @since 100.3.0
 */
interface RevertProductSaleInterface
{
    /**
     * Revert register product sale
     *
     * Method signature is unchanged for backward compatibility
     *
     * @param string[] $items
     * @param int $websiteId
     * @return bool
     * @since 100.3.0
     */
    public function revertProductsSale($items, $websiteId = null);
}
