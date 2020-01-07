<?php
namespace Magento\SharedCatalog\Model;

/**
 * Cache, index management.
 */
class SharedCatalogInvalidation
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Magento\CatalogPermissions\App\ConfigInterface
     */
    private $permissionsConfig;

    /**
     * @var \Magento\SharedCatalog\Model\Repository
     */
    private $sharedCatalogRepository;

    /**
     * SharedCatalogInvalidation constructor.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\CatalogPermissions\App\ConfigInterface $permissionsConfig
     * @param \Magento\SharedCatalog\Model\Repository $sharedCatalogRepository
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogPermissions\App\ConfigInterface $permissionsConfig,
        \Magento\SharedCatalog\Model\Repository $sharedCatalogRepository
    ) {
        $this->productRepository = $productRepository;
        $this->eventManager = $eventManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->permissionsConfig = $permissionsConfig;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Clean cache by tag.
     *
     * @param string $sku
     * @return void
     */
    public function cleanCacheByTag($sku)
    {
        /* @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($sku);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
    }

    /**
     * Invalidate index.
     *
     * @return void
     */
    public function invalidateIndexRegistryItem()
    {
        $this->indexerRegistry->get('catalog_category_product')->invalidate();
    }

    /**
     * Validate assign products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @param array $categoryIds
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function validateAssignProducts(array $products, array $categoryIds)
    {
        $skus = [];
        $missingCategoryIds = [];
        $affectedSkus = [];
        /* @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($products as $product) {
            $productCategoryIds = $product->getCategoryIds();
            $productSku = $product->getSku();
            $skus[] = $productSku;
            if (empty(array_intersect($productCategoryIds, $categoryIds))) {
                $missingCategoryIds = array_merge(
                    $missingCategoryIds,
                    array_diff($productCategoryIds, $categoryIds)
                );
            };
            $affectedSkus[$productSku] = $productCategoryIds;
        }
        if (!empty($missingCategoryIds)) {
            $errorSkus = [];
            foreach ($affectedSkus as $key => $affectedSku) {
                if (empty(array_diff($affectedSku, $missingCategoryIds))) {
                    $errorSkus[] = $key;
                }
            }
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'You must enable category permissions for the following products: %skus.',
                    ['skus' => implode(', ', array_unique($errorSkus))]
                )
            );
        }
        return $skus;
    }

    /**
     * Validate unassign products.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateUnassignProducts(array $products)
    {
        $skus = [];
        /* @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($products as $product) {
            $product = $this->checkProductExist($product->getSku());
            $skus[] = $product->getSku();
        }
        return $skus;
    }

    /**
     * Check product exist.
     *
     * @param string $sku
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Api\Data\ProductInterface $product
     */
    public function checkProductExist($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Requested product doesn\'t exist: %sku.',
                    ['sku' => $sku]
                )
            );
        }
        return $product;
    }

    /**
     * Regenerate.
     *
     * @param array|int $reindexCategoryIds
     * @return void
     */
    public function reindexCatalogPermissions($reindexCategoryIds)
    {
        if ($this->permissionsConfig->isEnabled()) {
            $indexer = $this->indexerRegistry->get(\Magento\CatalogPermissions\Model\Indexer\Category::INDEXER_ID);
            $indexer->reindexList($reindexCategoryIds);
        }
    }

    /**
     * Check Shared Catalog exist.
     *
     * @param int $sharedCatalogId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function checkSharedCatalogExist($sharedCatalogId)
    {
        try {
            $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw \Magento\Framework\Exception\NoSuchEntityException::singleField('id', $sharedCatalogId);
        }
        return $sharedCatalog;
    }
}
