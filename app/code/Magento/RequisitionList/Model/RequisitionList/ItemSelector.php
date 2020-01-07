<?php

namespace Magento\RequisitionList\Model\RequisitionList;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * This model is used for retrieving requisition list items together with associated products information.
 */
class ItemSelector
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items
     */
    private $requisitionListItemRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @param \Magento\RequisitionList\Model\RequisitionList\Items $requisitionListItemRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\RequisitionList\Model\RequisitionListItemProduct $requisitionListItemProduct
     */
    public function __construct(
        \Magento\RequisitionList\Model\RequisitionList\Items $requisitionListItemRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\RequisitionList\Model\RequisitionListItemProduct $requisitionListItemProduct
    ) {
        $this->requisitionListItemRepository = $requisitionListItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requisitionListItemProduct = $requisitionListItemProduct;
    }

    /**
     * Get all items for particular requisition list.
     *
     * @param int $requisitionListId
     * @param int $websiteId
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface[]
     */
    public function selectAllItemsFromRequisitionList($requisitionListId, $websiteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            RequisitionListItemInterface::REQUISITION_LIST_ID,
            $requisitionListId
        )->create();
        /** @var RequisitionListItemInterface[] $requestedItems */
        $requestedItems = $this->requisitionListItemRepository->getList($searchCriteria)->getItems();
        $this->attachProductsToItems($requestedItems, $websiteId, true);

        return $requestedItems;
    }

    /**
     * Get selected requisition list items by their IDs for particular requisition list.
     *
     * @param int $requisitionListId
     * @param array $itemIds
     * @param int $websiteId
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface[]
     */
    public function selectItemsFromRequisitionList($requisitionListId, array $itemIds, $websiteId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            RequisitionListItemInterface::REQUISITION_LIST_ID,
            $requisitionListId
        )->addFilter(
            RequisitionListItemInterface::REQUISITION_LIST_ITEM_ID,
            $itemIds,
            'in'
        )->create();
        /** @var RequisitionListItemInterface[] $requestedItems */
        $requestedItems = $this->requisitionListItemRepository->getList($searchCriteria)->getItems();
        $this->attachProductsToItems($requestedItems, $websiteId, false);

        return $requestedItems;
    }

    /**
     * Attach products information to requisition list items.
     *
     * @param RequisitionListItemInterface[] $requestedItems
     * @param int $websiteId
     * @param bool $loadProductOptions
     * @return void
     */
    private function attachProductsToItems(array $requestedItems, $websiteId, $loadProductOptions)
    {
        $productBySkus = $this->requisitionListItemProduct->extract(
            $requestedItems,
            $websiteId,
            $loadProductOptions
        );

        foreach ($requestedItems as $item) {
            if (isset($productBySkus[$item->getSku()])) {
                $this->requisitionListItemProduct->setProduct($item, $productBySkus[$item->getSku()]);
            }
            $this->requisitionListItemProduct->setIsProductAttached($item, isset($productBySkus[$item->getSku()]));
        }
    }
}
