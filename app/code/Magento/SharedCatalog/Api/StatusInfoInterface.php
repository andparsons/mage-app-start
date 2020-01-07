<?php
namespace Magento\SharedCatalog\Api;

/**
 * Service for getting and setting status for stared catalog module.
 *
 * @api
 * @since 100.0.0
 */
interface StatusInfoInterface
{
    /**
     * Get shared catalog module active status for scope.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isActive($scopeType, $scopeCode);

    /**
     * Get all store ids where Shared Catalog feature is switched on.
     *
     * @return array
     */
    public function getActiveSharedCatalogStoreIds();
}
