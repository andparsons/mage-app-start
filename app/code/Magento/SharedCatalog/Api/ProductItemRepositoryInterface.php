<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Api;

use \Magento\SharedCatalog\Api\Data\ProductItemInterface;

/**
 * Interface of repository for ProductItem model.
 * @api
 * @since 100.0.0
 */
interface ProductItemRepositoryInterface
{
    /**
     * Save product item.
     *
     * @param ProductItemInterface $sharedCatalogProductItem
     * @return int
     * @throws \Magento\Framework\Exception\InputException If product item is not populated properly
     * @throws \Magento\Framework\Exception\CouldNotSaveException If product item cannot be saved
     */
    public function save(ProductItemInterface $sharedCatalogProductItem);

    /**
     * Get product item by id.
     *
     * @param int $sharedCatalogProductItemId
     * @return ProductItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product item with specified id doesn't exist
     */
    public function get($sharedCatalogProductItemId);

    /**
     * Delete product item.
     *
     * @param ProductItemInterface $sharedCatalogProductItem
     * @return bool
     * @throws \Magento\Framework\Exception\StateException If product item cannot be deleted
     */
    public function delete(ProductItemInterface $sharedCatalogProductItem);

    /**
     * Delete product item by id.
     *
     * @param int $sharedCatalogProductItemId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product item with specified id doesn't exist
     * @throws \Magento\Framework\Exception\StateException If product item cannot be deleted
     */
    public function deleteById($sharedCatalogProductItemId);

    /**
     * Delete product items in bulk.
     *
     * @param ProductItemInterface[] $sharedCatalogProductItems
     * @return bool
     */
    public function deleteItems(array $sharedCatalogProductItems);

    /**
     * Get list of product items by specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SharedCatalog\Api\Data\ProductItemSearchResultsInterface
     * @throws \InvalidArgumentException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
