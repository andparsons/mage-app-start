<?php

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\DB\Select;

/**
 * @api
 * @since 100.1.0
 *
 * @deprecated 100.3.0 Replaced with Multi Source Inventory
 * @link https://devdocs.magento.com/guides/v2.3/inventory/index.html
 * @link https://devdocs.magento.com/guides/v2.3/inventory/catalog-inventory-replacements.html
 */
interface QueryProcessorInterface
{
    /**
     * @param Select $select
     * @param null|array $entityIds
     * @param bool $usePrimaryTable
     * @return Select
     * @since 100.1.0
     */
    public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false);
}
