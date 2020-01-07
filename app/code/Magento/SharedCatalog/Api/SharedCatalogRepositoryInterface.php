<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Api;

/**
 * @api
 * @since 100.0.0
 */
interface SharedCatalogRepositoryInterface
{
    /**
     * Create or update Shared Catalog service.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return int
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog);

    /**
     * Return the following properties for the selected shared catalog: ID, Store Group ID, Name, Type,
     * Description, Customer Group, Tax Class.
     *
     * @param int $sharedCatalogId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sharedCatalogId);

    /**
     * Delete Shared Catalog service.
     *
     * @param \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog);

    /**
     * Delete a shared catalog by ID.
     *
     * @param int $sharedCatalogId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sharedCatalogId);

    /**
     * Return the list of shared catalogs and basic properties for each catalog.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SharedCatalog\Api\Data\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
