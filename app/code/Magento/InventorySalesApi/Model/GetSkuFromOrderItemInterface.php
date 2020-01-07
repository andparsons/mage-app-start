<?php

declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

use Magento\Sales\Api\Data\OrderItemInterface;

interface GetSkuFromOrderItemInterface
{
    /**
     * @param OrderItemInterface $orderItem
     * @return string
     */
    public function execute(OrderItemInterface $orderItem): string;
}
