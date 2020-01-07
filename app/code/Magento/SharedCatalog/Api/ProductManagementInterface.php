<?php
namespace Magento\SharedCatalog\Api;

/**
 * Shared catalog products actions.
 * @api
 * @since 100.0.0
 */
interface ProductManagementInterface
{
    /**
     * Return the list of product SKUs in the selected shared catalog.
     *
     * @param int $id
     * @return string[]
     */
    public function getProducts($id);

    /**
     * Add products into the shared catalog.
     *
     * @param int $id
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @return bool true on success
     */
    public function assignProducts($id, array $products);

    /**
     * Remove the specified products from the shared catalog.
     *
     * @param int $id
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @return bool true on success
     */
    public function unassignProducts($id, array $products);

    /**
     * Reassign products to the shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @param array $skus
     * @return $this
     */
    public function reassignProducts(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog,
        array $skus
    );
}
