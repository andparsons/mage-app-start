<?php
declare(strict_types=1);

namespace Magento\InventoryApi\Model;

/**
 * Sugar service to retrieve source codes by a list of SKUs
 * @api
 */
interface GetSourceCodesBySkusInterface
{
    /**
     * @param array $skus
     * @return array
     */
    public function execute(array $skus): array;
}
