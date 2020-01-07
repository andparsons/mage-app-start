<?php

namespace Magento\RequisitionList\Model\RequisitionList;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\RequisitionList\Api\Data\RequisitionListInterface;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Class ItemsLoadHandler
 */
class ItemsLoadHandler
{
    /**
     * @var Items
     */
    private $requisitionListItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Items $requisitionListItemRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Items $requisitionListItemRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requisitionListItemRepository = $requisitionListItemRepository;
    }

    /**
     * Load requisition list items
     *
     * @param RequisitionListInterface $requisitionList
     * @return RequisitionListInterface
     */
    public function load(RequisitionListInterface $requisitionList)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            RequisitionListItemInterface::REQUISITION_LIST_ID,
            $requisitionList->getId()
        )->create();
        $items = $this->requisitionListItemRepository->getList($searchCriteria)->getItems();
        $requisitionList->setItems($items);
        return $requisitionList;
    }
}
