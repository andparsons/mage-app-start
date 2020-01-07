<?php
namespace Magento\SharedCatalog\Api;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Shared catalog prices actions.
 * @api
 * @since 100.0.0
 */
interface PriceManagementInterface
{
    /**
     * Save product tier prices.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $prices
     * @return $this
     */
    public function saveProductTierPrices(SharedCatalogInterface $sharedCatalog, array $prices);

    /**
     * Delete product tier prices.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $skus
     * @return $this
     */
    public function deleteProductTierPrices(SharedCatalogInterface $sharedCatalog, array $skus);
}
