<?php
namespace Magento\NegotiableQuoteSharedCatalog\Model\SharedCatalog\ProductItem;

use Magento\SharedCatalog\Api\Data\ProductItemInterface;

/**
 * Retrieve product items from shared catalog.
 */
class Retrieve
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
     * Retrieve product items from shared catalog by customer group and product sku.
     *
     * @param int $customerGroupId
     * @param string $sku
     * @return \Magento\SharedCatalog\Api\Data\ProductItemInterface[]
     */
    public function retrieve($customerGroupId, $sku)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProductItemInterface::CUSTOMER_GROUP_ID, $customerGroupId)
            ->addFilter(ProductItemInterface::SKU, $sku);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->sharedCatalogProductItemRepository->getList($searchCriteria);
        return $searchResults->getItems();
    }
}
