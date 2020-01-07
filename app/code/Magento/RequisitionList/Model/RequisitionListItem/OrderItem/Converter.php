<?php

namespace Magento\RequisitionList\Model\RequisitionListItem\OrderItem;

use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Creates requisition list item based on order item information.
 */
class Converter
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder
     */
    private $optionsBuilder;

    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * @param \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder $optionsBuilder
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory
     */
    public function __construct(
        \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder $optionsBuilder,
        \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory
    ) {
        $this->optionsBuilder = $optionsBuilder;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
    }

    /**
     * Create requisition list item based on order item information.
     *
     * @param OrderItemInterface $orderItem
     * @param string $sku
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface
     */
    public function convert(OrderItemInterface $orderItem, $sku)
    {
        $itemOptions = [];
        $productOptions = $orderItem->getProductOptions();

        if (!empty($productOptions['info_buyRequest'])) {
            $itemOptions = $this->optionsBuilder->build($productOptions['info_buyRequest'], 0, true);
        }

        /** @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterface $requisitionListItem */
        $requisitionListItem = $this->requisitionListItemFactory->create();
        $requisitionListItem->setQty($orderItem->getQtyOrdered());
        $requisitionListItem->setOptions($itemOptions);
        $requisitionListItem->setSku($sku);

        return $requisitionListItem;
    }
}
