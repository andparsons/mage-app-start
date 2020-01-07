<?php

namespace Magento\RequisitionList\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory;
use Magento\RequisitionList\Api\RequisitionListManagementInterface;
use Magento\RequisitionList\Api\RequisitionListRepositoryInterface;
use Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter;
use Magento\RequisitionList\Model\RequisitionListItem\Validation;
use Magento\RequisitionList\Model\RequisitionListItem\Merger as ItemMerger;

/**
 * Class is responsible for actions with requisition list items.
 */
class RequisitionListManagement implements RequisitionListManagementInterface
{
    /**
     * @var RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\CartItemConverter
     */
    private $cartItemConverter;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Validation
     */
    private $validation;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Merger
     */
    private $itemMerger;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $addToCartProcessors = [];

    /**
     * @var string
     */
    private $defaultAddToCartProcessorKey = 'simple';

    /**
     * @param RequisitionListRepositoryInterface $requisitionListRepository
     * @param RequisitionListItemInterfaceFactory $requisitionListItemFactory
     * @param CartRepositoryInterface $cartRepository
     * @param CartItemConverter $cartItemConverter
     * @param Validation $validation
     * @param ItemMerger $itemMerger
     * @param DateTime $dateTime
     * @param array $addToCartProcessors
     */
    public function __construct(
        RequisitionListRepositoryInterface $requisitionListRepository,
        RequisitionListItemInterfaceFactory $requisitionListItemFactory,
        CartRepositoryInterface $cartRepository,
        CartItemConverter $cartItemConverter,
        Validation $validation,
        ItemMerger $itemMerger,
        DateTime $dateTime,
        array $addToCartProcessors
    ) {
        $this->requisitionListRepository = $requisitionListRepository;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
        $this->cartRepository = $cartRepository;
        $this->cartItemConverter = $cartItemConverter;
        $this->validation = $validation;
        $this->itemMerger = $itemMerger;
        $this->dateTime = $dateTime;
        $this->addToCartProcessors = $addToCartProcessors;
    }

    /**
     * @inheritdoc
     */
    public function addItemToList(
        RequisitionListInterface $requisitionList,
        RequisitionListItemInterface $requisitionListItem
    ) {
        $items = $requisitionList->getItems();
        $requisitionListItems = $this->itemMerger->mergeItem($items, $requisitionListItem);
        $requisitionList->setItems($requisitionListItems);
        $this->requisitionListRepository->save($requisitionList);
        return $requisitionList;
    }

    /**
     * @inheritdoc
     */
    public function setItemsToList(
        RequisitionListInterface $requisitionList,
        array $requisitionListItems
    ) {
        $requisitionListItems = $this->itemMerger->merge($requisitionListItems);
        $requisitionList->setItems($requisitionListItems);
        return $requisitionList;
    }

    /**
     * @inheritdoc
     */
    public function copyItemToList(
        RequisitionListInterface $requisitionList,
        RequisitionListItemInterface $requisitionListItem
    ) {
        /** @var RequisitionListItemInterface $requisitionListItem */
        $targetListItem = $this->requisitionListItemFactory->create();
        $targetListItem->setQty($requisitionListItem->getQty());
        $targetListItem->setOptions((array)$requisitionListItem->getOptions());
        $targetListItem->setSku($requisitionListItem->getSku());

        $this->addItemToList($requisitionList, $targetListItem);

        return $requisitionList;
    }

    /**
     * @inheritdoc
     */
    public function placeItemsInCart($cartId, array $items, $isReplace = false)
    {
        $cart = $this->cartRepository->get($cartId);

        if ($isReplace) {
            /** @var $cart \Magento\Quote\Model\Quote */
            $cart->removeAllItems();
        }

        $addedItems = [];
        foreach ($items as $item) {
            if ($this->validation->isValid($item)) {
                $this->addItemToCart($cart, $item);
                $addedItems[] = $item;
            }
        }

        $this->updateListActivity($items);
        $this->cartRepository->save($cart);

        return $addedItems;
    }

    /**
     * Add requisition list item to cart.
     *
     * @param CartInterface $cart
     * @param RequisitionListItemInterface $item
     * @return $this
     */
    private function addItemToCart(CartInterface $cart, RequisitionListItemInterface $item)
    {
        $cartItem = $this->cartItemConverter->convert($item);
        $product = $cartItem->getData('product');
        $productType = $product->getTypeId();
        /**
         * @var \Magento\RequisitionList\Model\AddToCartProcessorInterface $addToCartProcessor
         */
        $addToCartProcessor = (isset($this->addToCartProcessors[$productType]))
            ? $this->addToCartProcessors[$productType]
            : $this->addToCartProcessors[$this->defaultAddToCartProcessorKey];
        $addToCartProcessor->execute($cart, $cartItem);

        return $this;
    }

    /**
     * Update requisition lists last activity.
     *
     * @param RequisitionListItemInterface[] $items
     * @return $this
     */
    private function updateListActivity(array $items)
    {
        $listIds = array_map(function (RequisitionListItemInterface $item) {
            return $item->getRequisitionListId();
        }, $items);
        $listIds = array_unique($listIds);

        foreach ($listIds as $listId) {
            $list = $this->requisitionListRepository->get($listId);
            $list->setUpdatedAt($this->dateTime->timestamp());
            $this->requisitionListRepository->save($list);
        }

        return $this;
    }
}
