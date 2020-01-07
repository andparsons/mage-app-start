<?php
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

interface IsProductAssignedToStockInterface
{
    /**
     * @param string $sku
     * @param int $stockId
     * @return bool
     */
    public function execute(string $sku, int $stockId): bool;
}
