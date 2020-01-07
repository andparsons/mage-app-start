<?php
namespace Magento\SharedCatalog\Model\Form\Storage;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Mass assignment of products to a shared catalog.
 */
class SharedCatalogMassAssignment
{
    /**
     * @var \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader
     */
    private $productTierPriceLoader;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogAssignment
     */
    private $sharedCatalogAssignment;

    /**
     * Batch size to iterate collection
     *
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader
     * @param \Magento\SharedCatalog\Model\SharedCatalogAssignment $sharedCatalogAssignment
     * @param int $batchSize defines how many items can be processed by one iteration
     */
    public function __construct(
        \Magento\SharedCatalog\Model\Price\ProductTierPriceLoader $productTierPriceLoader,
        \Magento\SharedCatalog\Model\SharedCatalogAssignment $sharedCatalogAssignment,
        int $batchSize = 5000
    ) {
        $this->productTierPriceLoader = $productTierPriceLoader;
        $this->sharedCatalogAssignment = $sharedCatalogAssignment;
        $this->batchSize = $batchSize;
    }

    /**
     * Mass assignment of products to a shared catalog.
     *
     * If $isAssign = true - adding products to a shared catalog
     * If $isAssign = false - removing products from a shared catalog
     * Populating storage with tier prices
     *
     * @param AbstractCollection $collection
     * @param \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage
     * @param int $sharedCatalogId
     * @param bool $isAssign
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assign(
        AbstractCollection $collection,
        \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage,
        $sharedCatalogId,
        $isAssign
    ) {
        $skus = [];
        $categoryIds = [];
        $collection->setPageSize($this->batchSize);
        $pages = $collection->getLastPageNumber();
        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $collection->setCurPage($currentPage);
            $skus = array_merge($skus, $this->sharedCatalogAssignment->getAssignProductsSku($collection->getItems()));
            if ($isAssign) {
                $categoryIds = array_merge(
                    $categoryIds,
                    $this->sharedCatalogAssignment->getAssignCategoryIdsByProducts(
                        $collection->addCategoryIds()->getItems()
                    )
                );
            }
            $collection->clear();
        }
        if ($isAssign) {
            $storage->assignProducts($skus);
            $storage->assignCategories(array_unique($categoryIds));
        } else {
            $storage->unassignProducts($skus);
        }

        $this->productTierPriceLoader->populateProductTierPrices(
            $collection->getItems(),
            $sharedCatalogId,
            $storage
        );
    }
}
