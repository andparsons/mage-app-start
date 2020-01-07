<?php
namespace Magento\SharedCatalog\Plugin;

use Magento\SharedCatalog\Api\Data\ProductItemInterface;

/**
 * Plugin updates shared catalog product item sku filed after product sku was updated.
 */
class UpdateItemsSku
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $productItemRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $productItemRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sharedCatalogProductItemRepository = $productItemRepository;
    }

    /**
     * Update shared catalog product item sku field after updating product sku.
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Product $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product) {
            $sku = $product->getOrigData(ProductItemInterface::SKU);

            if ($sku && $product->getSku() != $sku) {
                $this->updateProductItemsSku($sku, $product->getSku());
            }
        }

        return $product;
    }

    /**
     * Change shared catalog product item sku field value with sku=$sku to $updatedSku.
     *
     * @param string $sku
     * @param string $updatedSku
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updateProductItemsSku($sku, $updatedSku)
    {
        $this->searchCriteriaBuilder->addFilter(ProductItemInterface::SKU, $sku);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->sharedCatalogProductItemRepository->getList($searchCriteria);
        $sharedCatalogProductItems = $searchResults->getItems();

        if ($sharedCatalogProductItems) {
            foreach ($sharedCatalogProductItems as $sharedCatalogProductItem) {
                $sharedCatalogProductItem->setSku($updatedSku);
                $this->sharedCatalogProductItemRepository->save($sharedCatalogProductItem);
            }
        }

        return true;
    }
}
