<?php

namespace Magento\NegotiableQuoteSharedCatalog\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;

/**
 * Remove products from negotiable quotes if products were unassigned from shared catalog.
 */
class DeleteUnavailableNegotiableQuoteItems
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogProductsLoader
     */
    private $sharedCatalogProductsLoader;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete
     */
    private $itemDeleter;

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $config;

    /**
     * @var \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalogRetriever
     */
    private $sharedCatalogRetriever;

    /**
     * @var ProductResourceModel
     */
    private $productResourceModel;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $sharedCatalogProductsLoader
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $config
     * @param \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalogRetriever $sharedCatalogRetriever
     * @param ProductResourceModel $productResourceModel
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\SharedCatalog\Model\SharedCatalogProductsLoader $sharedCatalogProductsLoader,
        \Magento\NegotiableQuoteSharedCatalog\Model\QuoteManagement $quoteManagement,
        \Magento\NegotiableQuoteSharedCatalog\Model\NegotiableQuote\Item\Delete $itemDeleter,
        \Magento\SharedCatalog\Api\StatusInfoInterface $config,
        \Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalogRetriever $sharedCatalogRetriever,
        ProductResourceModel $productResourceModel
    ) {
        $this->productRepository = $productRepository;
        $this->sharedCatalogProductsLoader = $sharedCatalogProductsLoader;
        $this->quoteManagement = $quoteManagement;
        $this->itemDeleter = $itemDeleter;
        $this->config = $config;
        $this->sharedCatalogRetriever = $sharedCatalogRetriever;
        $this->productResourceModel = $productResourceModel;
    }

    /**
     * Remove product from negotiable quotes after unassigning product from shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject
     * @param bool $result
     * @param \Magento\SharedCatalog\Api\Data\ProductItemInterface $item
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject,
        $result,
        \Magento\SharedCatalog\Api\Data\ProductItemInterface $item
    ) {
        if ($result) {
            $quoteItems = $this->quoteManagement->retrieveQuoteItems(
                $item->getCustomerGroupId(),
                [$item->getId()],
                $this->config->getActiveSharedCatalogStoreIds()
            );
            $this->itemDeleter->deleteItems($quoteItems);
        }

        return $result;
    }

    /**
     * Remove products from negotiable quotes after unassigning products from shared catalog.
     *
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject
     * @param bool $result
     * @param \Magento\SharedCatalog\Api\Data\ProductItemInterface[] $items
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteItems(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $subject,
        $result,
        array $items
    ) {
        if ($result) {
            $skusByGroupId = [];
            foreach ($items as $productItem) {
                $skusByGroupId[(int) $productItem->getCustomerGroupId()][] = $productItem->getSku();
            }

            foreach ($skusByGroupId as $customerGroupId => $productSkus) {
                $products = $this->retrieveProductIds($productSkus, $customerGroupId);
                $quoteItems = $this->quoteManagement->retrieveQuoteItems(
                    $customerGroupId,
                    $products,
                    $this->config->getActiveSharedCatalogStoreIds()
                );
                $this->itemDeleter->deleteItems($quoteItems);
            }
        }

        return $result;
    }

    /**
     * Retrieve product ids by skus.
     *
     * @param array $productSkus
     * @param int $customerGroupId
     * @return array
     */
    private function retrieveProductIds(array $productSkus, int $customerGroupId): array
    {
        if (!$this->sharedCatalogRetriever->isSharedCatalogPresent($customerGroupId)) {
            $publicCatalog = $this->sharedCatalogRetriever->getPublicCatalog();
            $publicCatalogProductSkus = $this->sharedCatalogProductsLoader->getAssignedProductsSkus(
                (int) $publicCatalog->getCustomerGroupId()
            );
            $productSkus = array_diff($productSkus, $publicCatalogProductSkus);
        }

        $productIds = $this->productResourceModel->getProductsIdsBySkus($productSkus);

        return array_values($productIds);
    }
}
