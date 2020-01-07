<?php

namespace Magento\RequisitionList\Model;

/**
 * Class provides product SKUs by product IDs.
 */
class ProductSkuLocator
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get product SKUs by product IDs.
     *
     * @param array $productIds
     * @return array
     */
    public function getProductSkus(array $productIds)
    {
        $skuById = [];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in')->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();
        foreach ($products as $product) {
            $skuById[$product->getId()] = $product->getSku();
        }

        return $skuById;
    }
}
