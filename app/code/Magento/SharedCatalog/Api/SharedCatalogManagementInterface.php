<?php
namespace Magento\SharedCatalog\Api;

/**
 * Interface for managing shared catalog items.
 * @api
 * @since 100.0.0
 */
interface SharedCatalogManagementInterface
{
    /**
     * Get Public Shared Catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPublicCatalog();

    /**
     * Is Public Shared Catalog exist.
     *
     * @return bool
     */
    public function isPublicCatalogExist();
}
