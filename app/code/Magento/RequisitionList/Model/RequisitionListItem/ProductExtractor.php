<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class is responsible for loading products that are included in requisition list.
 */
class ProductExtractor
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface
     */
    private $productOptionRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get all products with options by requisition list items.
     *
     * @param array $productSkus
     * @param int $websiteId
     * @param bool $loadOptions [optional]
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function extract(array $productSkus, $websiteId, $loadOptions = true)
    {
        $productBySku = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(\Magento\Catalog\Api\Data\ProductInterface::SKU, $productSkus, 'in')
            ->addFilter('website_id', $websiteId, 'in')
            ->create();
        foreach ($this->productRepository->getList($searchCriteria)->getItems() as $product) {
            if ($loadOptions) {
                $options = $this->productOptionRepository->getProductOptions($product);
                $product->setOptions($options);
            }
            $productBySku[$product->getSku()] = $product;
        }

        return $productBySku;
    }
}
