<?php
namespace Magento\SharedCatalog\Api;

/**
 * @api
 * @since 100.0.0
 */
interface ProductItemManagementInterface
{
    /**
     * Id customer group for NOT LOGGED IN.
     */
    const CUSTOMER_GROUP_NOT_LOGGED_IN = 0;

    /**
     * Default value for quantity
     */
    const DEFAULT_QTY = 1;

    /**
     * Delete items by skus.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @param array $skus [optional]
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteItems(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog,
        array $skus = []
    );

    /**
     * Update Tier Prices.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $tierPricesData
     * @return $this
     */
    public function updateTierPrices(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $tierPricesData
    );

    /**
     * Delete Tier Prices by SKUs.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @param array $skus
     * @return $this
     */
    public function deleteTierPricesBySku(
        \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog,
        array $skus
    );

    /**
     * Add Items by skus.
     *
     * @param int $customerGroupId
     * @param array $skus
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addItems($customerGroupId, array $skus);

    /**
     * Save item.
     *
     * @param string $sku
     * @param int $customerGroupId
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveItem($sku, $customerGroupId);

    /**
     * Delete all tier prices for public catalog.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deletePricesForPublicCatalog();

    /**
     * Set all tier prices for public catalog assign to group NOT LOGGED IN and add products for NOT LOGGED IN group.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addPricesForPublicCatalog();
}
