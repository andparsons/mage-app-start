<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItemProduct;

/**
 * Class is responsible for merging items in requisition list.
 */
class Merger
{
    /**
     * @var RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @param RequisitionListItemProduct $requisitionListItemProduct
     */
    public function __construct(
        RequisitionListItemProduct $requisitionListItemProduct
    ) {
        $this->requisitionListItemProduct = $requisitionListItemProduct;
    }

    /**
     * Merge requisition list items if they contain the same products and product options.
     *
     * @param RequisitionListItemInterface[] $items
     * @return RequisitionListItemInterface[]
     */
    public function merge(array $items)
    {
        $mergedItems = [];
        foreach ($items as $item) {
            $isItemMerged = false;
            foreach ($mergedItems as $i => $mergedItem) {
                if ($this->isItemsProductEqual($item, $mergedItem)) {
                    $mergedItems[$i] = $this->mergeItems($item, $mergedItem);
                    $isItemMerged = true;
                }
            }
            if (!$isItemMerged) {
                $mergedItems[] = $item;
            }
        }

        return $mergedItems;
    }

    /**
     * Merge new requisition list item with existing ones.
     *
     * @param RequisitionListItemInterface[] $items
     * @param RequisitionListItemInterface $newItem
     * @return RequisitionListItemInterface[]
     */
    public function mergeItem(array $items, RequisitionListItemInterface $newItem)
    {
        $isItemMerged = false;
        foreach ($items as $index => $item) {
            if ($this->isItemsProductEqual($item, $newItem)) {
                $items[$index] = $this->mergeItems($item, $newItem);
                $isItemMerged = true;
                break;
            }
        }
        if (!$isItemMerged) {
            $items[] = $newItem;
        }

        return $items;
    }

    /**
     * Merge two requisition list items to one.
     *
     * @param RequisitionListItemInterface $srcItem
     * @param RequisitionListItemInterface $targetItem
     * @return RequisitionListItemInterface
     */
    private function mergeItems(RequisitionListItemInterface $srcItem, RequisitionListItemInterface $targetItem)
    {
        $qty = $targetItem->getQty() + $srcItem->getQty();
        $targetItem->setQty($qty);
        return $targetItem;
    }

    /**
     * Check if two requisition list items have the same products and options.
     *
     * @param RequisitionListItemInterface $srcItem
     * @param RequisitionListItemInterface $targetItem
     * @return bool
     */
    private function isItemsProductEqual(
        RequisitionListItemInterface $srcItem,
        RequisitionListItemInterface $targetItem
    ) {
        if ($srcItem->getSku() !== $targetItem->getSku()) {
            return false;
        }

        try {
            $srcProduct = $this->requisitionListItemProduct->getProduct($srcItem);
            $targetProduct = $this->requisitionListItemProduct->getProduct($targetItem);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        if ($srcProduct->getId() != $targetProduct->getId()) {
            return false;
        }

        $srcOptions = $srcProduct->getCustomOptions();
        $targetOptions = $targetProduct->getCustomOptions();

        return $this->compareOptions($srcOptions, $targetOptions) && $this->compareOptions($targetOptions, $srcOptions);
    }

    /**
     * Check if two options array are identical.
     * First options array is prerogative.
     * Second options array checked against first one.
     *
     * @param array $firstOptions
     * @param array $secondOptions
     * @return bool
     */
    private function compareOptions(array $firstOptions, array $secondOptions)
    {
        foreach ($firstOptions as $option) {
            $code = $option->getCode();
            if (in_array($code, ['info_buyRequest'])) {
                continue;
            }
            if (!isset($secondOptions[$code]) || $secondOptions[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }
}
