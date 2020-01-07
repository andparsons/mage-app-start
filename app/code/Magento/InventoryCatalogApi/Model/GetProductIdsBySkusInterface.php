<?php
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Provides all product SKUs by ProductIds. Key is sku, value is product id
 * @api
 */
interface GetProductIdsBySkusInterface
{
    /**
     * @param array $skus
     * @return array
     * @throws NoSuchEntityException
     */
    public function execute(array $skus): array;
}
