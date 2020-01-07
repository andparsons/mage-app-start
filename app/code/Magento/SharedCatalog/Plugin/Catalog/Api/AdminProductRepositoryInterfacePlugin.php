<?php
namespace Magento\SharedCatalog\Plugin\Catalog\Api;

/**
 * Class ProductRepositoryInterfacePlugin
 */
class AdminProductRepositoryInterfacePlugin
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    protected $sharedCatalogProductItemRepository;

    /**
     * Constructor for AdminProductRepositoryInterfacePlugin class
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
     * Remove products from shared catalog after it was deleted
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Closure $method
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $method,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $result = $method($product);
        $this->searchCriteriaBuilder
            ->addFilter(\Magento\SharedCatalog\Api\Data\ProductItemInterface::SKU, $product->getSku());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $items = $this->sharedCatalogProductItemRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $this->sharedCatalogProductItemRepository->delete($item);
        }
        return $result;
    }
}
