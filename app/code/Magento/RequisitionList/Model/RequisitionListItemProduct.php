<?php

namespace Magento\RequisitionList\Model;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Stores products assigned to the requisition list item.
 */
class RequisitionListItemProduct
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement
     */
    private $optionsManagement;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $requisitionListItemSettings = [];

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor
     */
    private $productExtractor;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param OptionsManagement $optionsManagement
     * @param \Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor $productExtractor
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\RequisitionList\Model\OptionsManagement $optionsManagement,
        \Magento\RequisitionList\Model\RequisitionListItem\ProductExtractor $productExtractor
    ) {
        $this->productRepository = $productRepository;
        $this->optionsManagement = $optionsManagement;
        $this->productExtractor = $productExtractor;
    }

    /**
     * Assign product to the requisition list item.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @param ProductInterface $product
     * @return void
     */
    public function setProduct(RequisitionListItemInterface $requisitionListItem, ProductInterface $product)
    {
        $itemId = $this->getRequisitionListItemId($requisitionListItem);
        $sku = $requisitionListItem->getSku();
        $this->products[$itemId]['product'][$sku] = $product;
    }

    /**
     * Get product assigned to the requisition list item.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @param bool $needReload [optional]
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct(RequisitionListItemInterface $requisitionListItem, $needReload = false)
    {
        $itemId = $this->getRequisitionListItemId($requisitionListItem);
        $sku = $requisitionListItem->getSku();

        if ($needReload || !isset($this->products[$itemId]['product'][$sku])) {
            $product = $this->productRepository->get($sku, false, null, true);
            $this->products[$itemId]['product'][$sku] = $product;
        }
        $product = $this->products[$itemId]['product'][$sku];
        $options = $this->optionsManagement->getOptions($requisitionListItem, $product);
        $product->setCustomOptions($options);

        return $product;
    }

    /**
     * Set value displaying that product is set for a requisition list item.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @param bool $isProductAttached
     * @return void
     */
    public function setIsProductAttached(RequisitionListItemInterface $requisitionListItem, $isProductAttached)
    {
        $itemId = $this->getRequisitionListItemId($requisitionListItem);
        $this->requisitionListItemSettings[$itemId] = $isProductAttached;
    }

    /**
     * Get value displaying that product is set for a requisition list item.
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return bool
     */
    public function isProductAttached(RequisitionListItemInterface $requisitionListItem)
    {
        $itemId = $this->getRequisitionListItemId($requisitionListItem);

        return $this->requisitionListItemSettings[$itemId] ?? false;
    }

    /**
     * Get all products with options by requisition list items.
     *
     * @param RequisitionListItemInterface[] $items
     * @param int $websiteId
     * @param bool $loadOptions [optional]
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function extract(array $items, $websiteId, $loadOptions = true)
    {
        $productSkus = array_map(
            function ($item) {
                return $item->getSku();
            },
            $items
        );

        return $this->productExtractor->extract($productSkus, $websiteId, $loadOptions);
    }

    /**
     * Get requisition list item id.
     *
     * Returns requisition list item id if it is set or 0 if requisitionListItem object is new
     *
     * @param RequisitionListItemInterface $requisitionListItem
     * @return int
     */
    private function getRequisitionListItemId(RequisitionListItemInterface $requisitionListItem)
    {
        return $requisitionListItem->getId() ?: 0;
    }
}
