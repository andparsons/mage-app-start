<?php
namespace Magento\SharedCatalog\Model\Form\Storage;

/**
 * Process changes between shared catalog and wizard storage.
 */
class DiffProcessor
{
    /**
     * @var \Magento\SharedCatalog\Api\CategoryManagementInterface
     */
    private $categoryManagement;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $productManagement;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk
     */
    private $scheduleBulk;

    /**
     * @param \Magento\SharedCatalog\Api\CategoryManagementInterface $categoryManagement
     * @param \Magento\SharedCatalog\Api\ProductManagementInterface $productManagement
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk $scheduleBulk
     */
    public function __construct(
        \Magento\SharedCatalog\Api\CategoryManagementInterface $categoryManagement,
        \Magento\SharedCatalog\Api\ProductManagementInterface $productManagement,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk $scheduleBulk
    ) {
        $this->categoryManagement = $categoryManagement;
        $this->productManagement = $productManagement;
        $this->scheduleBulk = $scheduleBulk;
    }

    /**
     * Get information whether categories, products or prices were changed.
     *
     * @param \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage
     * @param int $sharedCatalogId
     * @return array
     */
    public function getDiff(\Magento\SharedCatalog\Model\Form\Storage\Wizard $storage, $sharedCatalogId)
    {
        $origAssignedCategories = $this->categoryManagement->getCategories($sharedCatalogId);
        $origAssignedProducts = $this->productManagement->getProducts($sharedCatalogId);
        $prices = $storage->getTierPrices(null, true);
        $unassignProductSkus = $storage->getUnassignedProductSkus();
        $prices = array_diff_key($prices, array_flip($unassignProductSkus));

        return [
            'pricesChanged' => (bool)count($this->scheduleBulk->filterUnchangedPrices($prices)),
            'categoriesChanged' =>
                $this->categoriesChanged($storage->getAssignedCategoriesIds(), $origAssignedCategories),
            'productsChanged' => $this->productsChanged($storage->getAssignedProductSkus(), $origAssignedProducts)
        ];
    }

    /**
     * Check whether categories were changed.
     *
     * @param array $storageCategoriesIds
     * @param array $origAssignedCategories
     * @return bool
     */
    private function categoriesChanged(array $storageCategoriesIds, array $origAssignedCategories)
    {
        return array_diff($origAssignedCategories, $storageCategoriesIds)
        || array_diff($storageCategoriesIds, $origAssignedCategories);
    }

    /**
     * Check whether products were changed.
     *
     * @param array $storageProductsSkus
     * @param array $origAssignedProducts
     * @return bool
     */
    private function productsChanged(array $storageProductsSkus, array $origAssignedProducts)
    {
        return array_diff($origAssignedProducts, $storageProductsSkus)
        || array_diff($storageProductsSkus, $origAssignedProducts);
    }
}
