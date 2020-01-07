<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Api;

/**
 * Class for processing Shared Catalog duplication actions
 */
interface SharedCatalogDuplicationInterface
{
    /**
     * Add products into the shared catalog by catalog ID and array of the products sku.
     *
     * @param int $sharedCatalogId
     * @param string[] $productsSku
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assignProductsToDuplicate(int $sharedCatalogId, array $productsSku): void;
}
