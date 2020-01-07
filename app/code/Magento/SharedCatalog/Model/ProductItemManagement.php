<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\ProductItemManagementInterface;
use Magento\SharedCatalog\Api\Data\ProductItemInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Manage products assigned to shared catalog and their tier prices.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductItemManagement implements ProductItemManagementInterface
{
    /**
     * Equal condition for sql.
     */
    const EQUAL_VALUE = 'eq';

    /**
     * In condition for sql.
     */
    const IN_VALUE = 'in';

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    private $sharedCatalogProductItemRepository;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemFactory
     */
    private $sharedCatalogProductItemFactory;

    /**
     * @var \Magento\SharedCatalog\Model\TierPriceManagement
     */
    private $sharedCatalogTierPriceManagement;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader
     */
    private $sharedCatalogProductsLoader;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem
     */
    private $productItemResource;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\SharedCatalog\Model\ProductItemFactory $sharedCatalogProductItemFactory
     * @param \Magento\SharedCatalog\Model\TierPriceManagement $sharedCatalogTierPriceManagement
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement
     * @param SharedCatalogProductsLoader $sharedCatalogProductsLoader
     * @param \Magento\SharedCatalog\Model\ResourceModel\ProductItem $productItemResource
     * @param int $batchSize defines how many items can be processed by one iteration
     */
    public function __construct(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $sharedCatalogProductItemRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SharedCatalog\Model\ProductItemFactory $sharedCatalogProductItemFactory,
        \Magento\SharedCatalog\Model\TierPriceManagement $sharedCatalogTierPriceManagement,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement,
        SharedCatalogProductsLoader $sharedCatalogProductsLoader,
        \Magento\SharedCatalog\Model\ResourceModel\ProductItem $productItemResource,
        $batchSize = 5000
    ) {
        $this->sharedCatalogProductItemRepository = $sharedCatalogProductItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sharedCatalogProductItemFactory = $sharedCatalogProductItemFactory;
        $this->sharedCatalogTierPriceManagement = $sharedCatalogTierPriceManagement;
        $this->sharedCatalogManagement = $sharedCatalogManagement;
        $this->sharedCatalogProductsLoader = $sharedCatalogProductsLoader;
        $this->productItemResource = $productItemResource;
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function deleteItems(SharedCatalogInterface $sharedCatalog, array $skus = [])
    {
        $productItems = $this->loadItems($sharedCatalog->getCustomerGroupId(), $skus);
        if ($sharedCatalog->getType() == SharedCatalogInterface::TYPE_PUBLIC) {
            $productItems += $this->loadItems(self::CUSTOMER_GROUP_NOT_LOGGED_IN, $skus);
        }
        while (count($productItems)) {
            $productItemsBatch = array_splice($productItems, 0, $this->batchSize);
            $productSkus = array_map(
                function ($productItem) {
                    return $productItem->getSku();
                },
                $productItemsBatch
            );
            $this->sharedCatalogTierPriceManagement->deleteProductTierPrices($sharedCatalog, $productSkus, true);
            $this->sharedCatalogProductItemRepository->deleteItems($productItemsBatch);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function updateTierPrices(
        SharedCatalogInterface $sharedCatalog,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        array $tierPricesData
    ) {
        $this->sharedCatalogTierPriceManagement->deleteProductTierPrices($sharedCatalog, [$product->getSku()]);
        $this->sharedCatalogTierPriceManagement->updateProductTierPrices(
            $sharedCatalog,
            $product->getSku(),
            $tierPricesData
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function deleteTierPricesBySku(SharedCatalogInterface $sharedCatalog, array $skus)
    {
        $this->sharedCatalogTierPriceManagement->deleteProductTierPrices($sharedCatalog, $skus);
        return $this;
    }

    /**
     * Load link items between shared catalog and products.
     *
     * @param int $customerGroupId
     * @param array $skuList [optional]
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface[]
     * @throws LocalizedException
     */
    private function loadItems($customerGroupId, array $skuList = [])
    {
        $this->searchCriteriaBuilder->addFilter(
            ProductItemInterface::CUSTOMER_GROUP_ID,
            $customerGroupId,
            self::EQUAL_VALUE
        );
        if (!empty($skuList)) {
            $this->searchCriteriaBuilder->addFilter(ProductItemInterface::SKU, $skuList, self::IN_VALUE);
        }
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setPageSize($this->batchSize);
        try {
            $currentPage = 1;
            $products = [];
            $totalCount = null;
            do {
                $searchCriteria->setCurrentPage($currentPage++);
                $searchResults = $this->sharedCatalogProductItemRepository->getList($searchCriteria);
                $productItems = $searchResults->getItems();
                if (count($productItems)) {
                    $products = array_merge($products, $productItems);
                }
                $totalCount = null === $totalCount
                    ? $searchResults->getTotalCount() - $this->batchSize
                    : $totalCount - $this->batchSize;
            } while ($totalCount > 0);
        } catch (\InvalidArgumentException $e) {
            throw new LocalizedException(__('Cannot load product items for shared catalog'));
        }
        return $products;
    }

    /**
     * @inheritdoc
     */
    public function addItems($customerGroupId, array $skus)
    {
        $productItems = $this->loadItems($customerGroupId, $skus);
        if (count($productItems) !== count($skus)) {
            $notExistSkus = $this->deleteExistItemsFromSkus($productItems, $skus);
            foreach (array_chunk($notExistSkus, $this->batchSize) as $productsBatch) {
                $this->productItemResource->createItems($productsBatch, $customerGroupId);
            }
        }
        return $this;
    }

    /**
     * Delete items from skus list.
     *
     * @param ProductItemInterface[] $productItems
     * @param array $skus
     * @return array
     */
    private function deleteExistItemsFromSkus(array $productItems, array $skus)
    {
        foreach ($productItems as $item) {
            $key = array_search($item->getSku(), $skus);
            if ($key !== false) {
                unset($skus[$key]);
            }
        }
        return $skus;
    }

    /**
     * @inheritdoc
     */
    public function saveItem($sku, $customerGroupId)
    {
        /** @var \Magento\SharedCatalog\Api\Data\ProductItemInterface $link */
        $link = $this->sharedCatalogProductItemFactory->create();
        $link->setSku($sku);
        $link->setCustomerGroupId($customerGroupId);
        $this->sharedCatalogProductItemRepository->save($link);
    }

    /**
     * @inheritdoc
     */
    public function deletePricesForPublicCatalog()
    {
        $customerGroupId = \Magento\SharedCatalog\Api\ProductItemManagementInterface::CUSTOMER_GROUP_NOT_LOGGED_IN;
        $productItems = $this->loadItems($customerGroupId);
        while (count($productItems)) {
            $productItemsBatch = array_splice($productItems, 0, $this->batchSize);
            $productSkus = array_map(
                function ($productItem) {
                    return $productItem->getSku();
                },
                $productItemsBatch
            );
            $this->sharedCatalogTierPriceManagement->deletePublicTierPrices($productSkus);
            $this->sharedCatalogProductItemRepository->deleteItems($productItemsBatch);
        }
    }

    /**
     * @inheritdoc
     */
    public function addPricesForPublicCatalog()
    {
        $sharedCatalog = $this->sharedCatalogManagement->getPublicCatalog();
        $customerGroupId = $sharedCatalog->getCustomerGroupId();
        $skus = $this->sharedCatalogProductsLoader->getAssignedProductsSkus(
            $sharedCatalog->getCustomerGroupId()
        );
        $this->sharedCatalogTierPriceManagement->addPricesForPublicCatalog($customerGroupId, $skus);
        $this->addItems(
            \Magento\SharedCatalog\Api\ProductItemManagementInterface::CUSTOMER_GROUP_NOT_LOGGED_IN,
            $skus
        );
    }
}
