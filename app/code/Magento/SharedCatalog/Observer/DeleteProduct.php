<?php

namespace Magento\SharedCatalog\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class DeleteProduct
 */
class DeleteProduct implements ObserverInterface
{
    /**
     * @var \Magento\SharedCatalog\Api\ProductItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $itemRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\SharedCatalog\Api\ProductItemRepositoryInterface $itemRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->itemRepository = $itemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Clean up shared catalog items that depends on the product.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        $sku = $product->getSku();
        $this->searchCriteriaBuilder
            ->addFilter(\Magento\SharedCatalog\Api\Data\ProductItemInterface::SKU, $sku);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $items = $this->itemRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $this->itemRepository->delete($item);
        }
        return $this;
    }
}
