<?php

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Get source item qty available for usage in SSA
 *
 * @api
 */
interface GetSourceItemQtyAvailableInterface
{
    /**
     * @param SourceItemInterface $sourceItem
     *
     * @return float
     */
    public function execute(SourceItemInterface $sourceItem): float;
}
