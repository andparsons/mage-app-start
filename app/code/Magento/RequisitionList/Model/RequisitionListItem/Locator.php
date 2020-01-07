<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

/**
 * Load requisition list item or create new one.
 */
class Locator
{
    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items
     */
    private $requisitionListItemRepository;

    /**
     * @var \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory
     */
    private $requisitionListItemFactory;

    /**
     * @param \Magento\RequisitionList\Model\RequisitionList\Items $requisitionListItemRepository
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory
     */
    public function __construct(
        \Magento\RequisitionList\Model\RequisitionList\Items $requisitionListItemRepository,
        \Magento\RequisitionList\Api\Data\RequisitionListItemInterfaceFactory $requisitionListItemFactory
    ) {
        $this->requisitionListItemRepository = $requisitionListItemRepository;
        $this->requisitionListItemFactory = $requisitionListItemFactory;
    }

    /**
     * Load requisition list item by id or create new one.
     *
     * @param int $itemId
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface
     */
    public function getItem($itemId)
    {
        if ($itemId) {
            return $this->requisitionListItemRepository->get($itemId);
        }

        return $this->requisitionListItemFactory->create();
    }
}
