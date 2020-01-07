<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Preparing sets of products and categories for assignment to shared catalog.
 */
class SharedCatalogAssignment
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface
     */
    private $productManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogInvalidation
     */
    private $sharedCatalogInvalidation;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * Batch size to iterate collection
     *
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\SharedCatalog\Api\ProductManagementInterface $productManagement
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogInvalidation $sharedCatalogInvalidation
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param int $batchSize defines how many items can be processed by one iteration
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\SharedCatalog\Api\ProductManagementInterface $productManagement,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository,
        \Magento\SharedCatalog\Model\SharedCatalogInvalidation $sharedCatalogInvalidation,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory = null,
        int $batchSize = 5000
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->productManagement = $productManagement;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->sharedCatalogProductItemRepository = $sharedCatalogProductItemRepository;
        $this->sharedCatalogInvalidation = $sharedCatalogInvalidation;
        $this->productCollectionFactory = $productCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
        $this->batchSize = $batchSize;
    }

    /**
     * Assign products to shared catalog categories.
     *
     * @param int $sharedCatalogId
     * @param array $assignCategoriesIds
     * @return void
     */
    public function assignProductsForCategories($sharedCatalogId, array $assignCategoriesIds)
    {
        $products = $this->getProductsByCategoryIds($assignCategoriesIds);
        if (!empty($products)) {
            $this->productManagement->assignProducts($sharedCatalogId, $products);
        }
    }

    /**
     * Unassign products from shared catalog categories.
     *
     * @param int $sharedCatalogId
     * @param array $unassignCategoriesIds
     * @param array $assignCategoriesIds
     * @return void
     */
    public function unassignProductsForCategories(
        $sharedCatalogId,
        array $unassignCategoriesIds,
        array $assignCategoriesIds
    ) {
        $products = $this->getUnassignProductsByCategoryIds(
            $sharedCatalogId,
            $unassignCategoriesIds,
            $assignCategoriesIds
        );

        if (!empty($products)) {
            $this->productManagement->unassignProducts($sharedCatalogId, $products);
        }
    }

    /**
     * Get categories IDs to be assigned to shared catalog for provided products SKUs.
     *
     * @param array $assignProductsSkus
     * @return array
     */
    public function getAssignCategoryIdsByProductSkus(array $assignProductsSkus)
    {
        $productsCollection = $this->productCollectionFactory->create();
        $productsCollection->addFieldToFilter('sku', ['in' => $assignProductsSkus]);
        $productsCollection->setPageSize($this->batchSize);

        $pages = $productsCollection->getLastPageNumber();
        $categoryIds = [];
        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $productsCollection->setCurPage($currentPage);
            $productsCollection->load()->addCategoryIds();
            $categoryIds = array_merge(
                $categoryIds,
                $this->getAssignCategoryIdsByProducts($productsCollection->getItems())
            );
            $productsCollection->clear();
        }

        return array_unique($categoryIds);
    }

    /**
     * Get categories IDs to be assigned to shared catalog for provided products.
     *
     * @param array $assignProducts
     * @return array
     */
    public function getAssignCategoryIdsByProducts(array $assignProducts)
    {
        $assignCategoryIds = [];
        foreach ($assignProducts as $product) {
            $assignCategoryIds[] = $product->getCategoryIds();
        }
        if (!empty($assignCategoryIds)) {
            $assignCategoryIds = array_merge(...$assignCategoryIds);
        }
        return array_unique($assignCategoryIds);
    }

    /**
     * Get products SKUs to be assigned to shared catalog for provided products.
     *
     * @param array $assignProducts
     * @return array
     */
    public function getAssignProductsSku(array $assignProducts)
    {
        $assignProductsSku = [];
        foreach ($assignProducts as $product) {
            $sku = $product->getSku();
            $assignProductsSku[$sku] = $sku;
        }
        return $assignProductsSku;
    }

    /**
     * Get products for provided categories IDs.
     *
     * @param array $categoriesIds
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    private function getProductsByCategoryIds(array $categoriesIds)
    {
        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('category_id', $categoriesIds, 'in')
            ->create();
        return $this->productRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Get products SKUs to be assigned to shared catalog for provided categories IDs.
     *
     * @param array $assignCategoriesIds
     * @return array
     */
    public function getAssignProductSkusByCategoryIds(array $assignCategoriesIds)
    {
        $productsCollection = $this->productCollectionFactory->create();
        $productsCollection->addCategoriesFilter(['in' => $assignCategoriesIds]);
        $productSkus = [];
        foreach ($productsCollection->getItems() as $product) {
            $productSkus[] = $product->getSku();
        }
        return $productSkus;
    }

    /**
     * Get product items to be assigned to shared catalog for provided categories IDs.
     *
     * Will return an array with SKUs and category ids of the products assigned to categories which were provided in the
     * method parameters.
     * New collection will be created for each category (to avoid building of massive SQL queries).
     * Collection iterations will be also paged by the $this->batchSize for a cases when to many products are assigned
     * for the category.
     *
     * @param array $assignCategoriesIds
     * @return array
     */
    public function getAssignProductsByCategoryIds(array $assignCategoriesIds): array
    {
        $productSkus = [];
        $productCategories = [];
        foreach ($assignCategoriesIds as $categoryId) {
            $productsCollection = $this->productCollectionFactory->create();
            $productsCollection->setPageSize($this->batchSize)->addCategoriesFilter(['in' => $categoryId]);
            $pages = $productsCollection->getLastPageNumber();
            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $productsCollection->setCurPage($currentPage);
                $productItems = $productsCollection->load()->addCategoryIds()->getItems();
                if (0 !== count($productItems)) {
                    $productSkus = array_merge($productSkus, $this->getAssignProductsSku($productItems));
                    $productCategories = array_merge(
                        $productCategories,
                        $this->getAssignCategoryIdsByProducts($productItems)
                    );
                }
                $productsCollection->clear();
            }
        }
        $products = [
            'skus' => $productSkus,
            'category_ids' => array_unique($productCategories)
        ];

        return $products;
    }

    /**
     * Get products to be unassigned from shared catalog when categories are unassigned.
     *
     * @param int $sharedCatalogId
     * @param array $unassignCategoriesIds
     * @param array $assignedCategoriesIds
     * @return array
     */
    private function getUnassignProductsByCategoryIds(
        $sharedCatalogId,
        array $unassignCategoriesIds,
        array $assignedCategoriesIds
    ) {
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        $assignedCategoriesIds = array_diff($assignedCategoriesIds, $unassignCategoriesIds);
        $unassignProducts = [];
        $this->searchCriteriaBuilder->addFilter('customer_group_id', $sharedCatalog->getCustomerGroupId());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        foreach ($this->sharedCatalogProductItemRepository->getList($searchCriteria)->getItems() as $product) {
            $product = $this->sharedCatalogInvalidation->checkProductExist($product->getSku());
            if (empty(array_intersect($product->getCategoryIds(), $assignedCategoriesIds))) {
                $unassignProducts[] = $product;
            }
        }
        return $unassignProducts;
    }

    /**
     * Get products SKUs to unassign them from shared catalog.
     *
     * Will return products SKUs which should be to be unassigned from shared catalog when a category is unselected
     * during shared catalog configuration.
     *
     * @param array $unassignCategoriesIds
     * @param array $assignedCategoriesIds
     * @return array
     */
    public function getProductSkusToUnassign(array $unassignCategoriesIds, array $assignedCategoriesIds)
    {
        $unassignProductsIds = [];
        foreach ($unassignCategoriesIds as $categoryId) {
            $productsCollection = $this->productCollectionFactory->create();
            $productsCollection->setPageSize($this->batchSize)->addCategoriesFilter(['in' => $categoryId]);
            $pages = $productsCollection->getLastPageNumber();
            for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
                $productsCollection->setCurPage($currentPage);
                foreach ($productsCollection->load()->addCategoryIds()->getItems() as $product) {
                    if (empty(array_intersect($product->getCategoryIds(), $assignedCategoriesIds))) {
                        $unassignProductsIds[$product->getSku()] = $product->getSku();
                    }
                }
                $productsCollection->clear();
            }
        }

        return $unassignProductsIds;
    }
}
