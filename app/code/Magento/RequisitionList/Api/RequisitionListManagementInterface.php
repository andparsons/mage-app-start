<?php

namespace Magento\RequisitionList\Api;

use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Interface for managing Requisition Lists.
 *
 * @api
 * @since 100.0.0
 */
interface RequisitionListManagementInterface
{
    /**
     * Add item to list.
     *
     * @param RequisitionListInterface $requisitionList
     * @param RequisitionListItemInterface $requisitionListItem
     * @return RequisitionListInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addItemToList(
        RequisitionListInterface $requisitionList,
        RequisitionListItemInterface $requisitionListItem
    );

    /**
     * Set items to list.
     *
     * @param RequisitionListInterface $requisitionList
     * @param RequisitionListItemInterface[] $requisitionListItems
     * @return RequisitionListInterface
     */
    public function setItemsToList(
        RequisitionListInterface $requisitionList,
        array $requisitionListItems
    );

    /**
     * Copy item to the requisition list preserving options and quantity.
     *
     * @param RequisitionListInterface $requisitionList
     * @param RequisitionListItemInterface $requisitionListItem
     * @return RequisitionListInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function copyItemToList(
        RequisitionListInterface $requisitionList,
        RequisitionListItemInterface $requisitionListItem
    );

    /**
     * Place requisition list items in shopping cart.
     *
     * @param int $cartId
     * @param RequisitionListItemInterface[] $items
     * @param bool $isReplace [optional]
     * @return RequisitionListItemInterface[] items that were added to cart
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function placeItemsInCart($cartId, array $items, $isReplace = false);
}
