<?php

namespace Magento\RequisitionList\Model\Checker;

/**
 * Interface for checking availability of edit requisition list product qty.
 */
interface ProductQtyChangeAvailabilityInterface
{
    /**
     * Check if product qty for requisition list item can be changed.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    public function isAvailable(\Magento\Catalog\Api\Data\ProductInterface $product);
}
