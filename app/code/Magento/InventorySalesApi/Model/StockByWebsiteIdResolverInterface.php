<?php
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

use Magento\InventoryApi\Api\Data\StockInterface;

/**
 * Resolve Stock by Website ID
 */
interface StockByWebsiteIdResolverInterface
{
    /**
     * @param int $websiteId
     * @return StockInterface
     */
    public function execute(int $websiteId): StockInterface;
}
