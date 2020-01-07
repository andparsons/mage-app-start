<?php
namespace Magento\SharedCatalog\Model;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SharedCatalog\Api\CategoryManagementInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Api\ProductItemManagementInterface;
use Magento\SharedCatalog\Api\ProductItemRepositoryInterface;
use Magento\SharedCatalog\Api\ProductManagementInterface;
use Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface;

/**
 * Shared catalog products actions.
 */
class ProductManagement implements ProductManagementInterface
{
    /**
     * @var SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var ProductItemManagementInterface
     */
    private $sharedCatalogProductItemManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductSharedCatalogsLoader
     */
    private $productSharedCatalogsLoader;

    /**
     * @var ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var SharedCatalogInvalidation
     */
    private $sharedCatalogInvalidation;

    /**
     * @var CategoryManagementInterface
     */
    private $sharedCatalogCategoryManagement;

    /**
     * Batch size to iterate collection
     *
     * @var int
     */
    private $batchSize;

    /**
     * ProductSharedCatalogsManagement constructor.
     *
     * @param SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param ProductItemManagementInterface $productItemManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProductSharedCatalogsLoader $productSharedCatalogsLoader
     * @param ProductItemRepositoryInterface $productItemRepository
     * @param SharedCatalogInvalidation $sharedCatalogInvalidation
     * @param CategoryManagementInterface $sharedCatalogCategoryManagement
     * @param int $batchSize defines how many items can be processed by one iteration
     */
    public function __construct(
        SharedCatalogRepositoryInterface $sharedCatalogRepository,
        ProductItemManagementInterface $productItemManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProductSharedCatalogsLoader $productSharedCatalogsLoader,
        ProductItemRepositoryInterface $productItemRepository,
        SharedCatalogInvalidation $sharedCatalogInvalidation,
        CategoryManagementInterface $sharedCatalogCategoryManagement,
        int $batchSize = 5000
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->sharedCatalogProductItemManagement = $productItemManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productSharedCatalogsLoader = $productSharedCatalogsLoader;
        $this->sharedCatalogProductItemRepository = $productItemRepository;
        $this->sharedCatalogInvalidation = $sharedCatalogInvalidation;
        $this->sharedCatalogCategoryManagement = $sharedCatalogCategoryManagement;
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function getProducts($id)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $this->searchCriteriaBuilder->addFilter(
            ProductItemInterface::CUSTOMER_GROUP_ID,
            $sharedCatalog->getCustomerGroupId()
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setPageSize($this->batchSize);

        $currentPage = 1;
        $productsSku = [];
        $totalCount = null;
        do {
            $searchCriteria->setCurrentPage($currentPage++);
            $searchResults = $this->sharedCatalogProductItemRepository->getList($searchCriteria);
            $productItems = $searchResults->getItems();
            if (count($productItems)) {
                $productsSku = array_merge($productsSku, $this->prepareProductSkus($productItems));
            }
            $totalCount = null === $totalCount
                ? $searchResults->getTotalCount() - $this->batchSize
                : $totalCount - $this->batchSize;
        } while ($totalCount > 0);

        return $productsSku;
    }

    /**
     * @inheritdoc
     */
    public function assignProducts($id, array $products)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $categoryIds = $this->sharedCatalogCategoryManagement->getCategories($sharedCatalog->getId());
        $skus = $this->sharedCatalogInvalidation->validateAssignProducts($products, $categoryIds);
        $customerGroupIds = $this->getAssociatedCustomerGroupIds($sharedCatalog);
        foreach ($customerGroupIds as $customerGroupId) {
            $this->sharedCatalogProductItemManagement->addItems($customerGroupId, $skus);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function unassignProducts($id, array $products)
    {
        $sharedCatalog = $this->sharedCatalogInvalidation->checkSharedCatalogExist($id);
        $skus = $this->sharedCatalogInvalidation->validateUnassignProducts($products);
        $customerGroupIds = $this->getAssociatedCustomerGroupIds($sharedCatalog);
        foreach ($customerGroupIds as $customerGroupId) {
            $this->deleteProductItems($customerGroupId, $skus, 'in');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateProductSharedCatalogs($sku, array $sharedCatalogIds)
    {
        $assignedSharedCatalogs = $this->productSharedCatalogsLoader->getAssignedSharedCatalogs($sku);

        $forCreate = array_diff($sharedCatalogIds, array_keys($assignedSharedCatalogs));
        if (!empty($forCreate)) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SharedCatalogInterface::SHARED_CATALOG_ID, $forCreate, 'in')
                ->create();
            $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria)->getItems();
            foreach ($sharedCatalogs as $sharedCatalog) {
                $customerGroups = $this->getAssociatedCustomerGroupIds($sharedCatalog);
                foreach ($customerGroups as $customerGroup) {
                    $this->sharedCatalogProductItemManagement->saveItem($sku, $customerGroup);
                }
            }
        }

        $forDelete = array_diff_key($assignedSharedCatalogs, array_flip($sharedCatalogIds));
        foreach ($forDelete as $sharedCatalog) {
            $this->sharedCatalogProductItemManagement->deleteItems($sharedCatalog, [$sku]);
        }
    }

    /**
     * Reassign products to shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @param array $skus
     * @return $this
     */
    public function reassignProducts(SharedCatalogInterface $sharedCatalog, array $skus)
    {
        $customerGroupIds = $this->getAssociatedCustomerGroupIds($sharedCatalog);
        foreach ($customerGroupIds as $customerGroupId) {
            $this->deleteProductItems($customerGroupId, $skus);
            $this->sharedCatalogProductItemManagement->addItems($customerGroupId, $skus);
        }

        return $this;
    }

    /**
     * Delete product items from shared catalog.
     *
     * @param int $customerGroupId
     * @param array $skus [optional]
     * @param string $conditionType [optional]
     * @return $this
     */
    private function deleteProductItems(int $customerGroupId, array $skus = [], string $conditionType = 'nin')
    {
        $this->searchCriteriaBuilder->addFilter(ProductItemInterface::CUSTOMER_GROUP_ID, $customerGroupId);
        if (!empty($skus)) {
            $this->searchCriteriaBuilder->addFilter(ProductItemInterface::SKU, $skus, $conditionType);
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $productItems = $this->sharedCatalogProductItemRepository->getList($searchCriteria)->getItems();
        $this->sharedCatalogProductItemRepository->deleteItems($productItems);
        foreach ($productItems as $productItem) {
            $this->sharedCatalogInvalidation->cleanCacheByTag($productItem->getSku());
        }
        $this->sharedCatalogInvalidation->invalidateIndexRegistryItem();

        return $this;
    }

    /**
     * Prepare product skus array.
     *
     * @param ProductItemInterface[] $products
     * @return string[]
     */
    private function prepareProductSkus(array $products): array
    {
        $productsSkus = [];
        foreach ($products as $product) {
            $productsSkus[] = $product->getSku();
        }

        return $productsSkus;
    }

    /**
     * Get customer group ids that associated with shared catalog.
     *
     * @param SharedCatalogInterface $sharedCatalog
     * @return int[]
     */
    private function getAssociatedCustomerGroupIds(SharedCatalogInterface $sharedCatalog): array
    {
        $customerGroupIds = [(int) $sharedCatalog->getCustomerGroupId()];
        if ($sharedCatalog->getType() == SharedCatalogInterface::TYPE_PUBLIC) {
            $customerGroupIds[] = GroupInterface::NOT_LOGGED_IN_ID;
        }

        return $customerGroupIds;
    }
}
