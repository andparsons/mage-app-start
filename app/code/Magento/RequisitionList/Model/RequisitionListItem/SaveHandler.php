<?php

namespace Magento\RequisitionList\Model\RequisitionListItem;

/**
 * Prepare and save requisition list item.
 */
class SaveHandler
{
    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder
     */
    private $optionsBuilder;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface
     */
    private $requisitionListManagement;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Locator
     */
    private $requisitionListItemLocator;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListProduct
     */
    private $requisitionListProduct;

    /**
     * @param \Magento\RequisitionList\Api\RequisitionListRepositoryInterface $requisitionListRepository
     * @param Options\Builder $optionsBuilder
     * @param \Magento\RequisitionList\Api\RequisitionListManagementInterface $requisitionListManagement
     * @param Locator $requisitionListItemLocator
     * @param \Magento\RequisitionList\Model\RequisitionListProduct $requisitionListProduct
     */
    public function __construct(
        \Magento\RequisitionList\Api\RequisitionListRepositoryInterface $requisitionListRepository,
        \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder $optionsBuilder,
        \Magento\RequisitionList\Api\RequisitionListManagementInterface $requisitionListManagement,
        \Magento\RequisitionList\Model\RequisitionListItem\Locator $requisitionListItemLocator,
        \Magento\RequisitionList\Model\RequisitionListProduct $requisitionListProduct
    ) {
        $this->requisitionListRepository = $requisitionListRepository;
        $this->optionsBuilder = $optionsBuilder;
        $this->requisitionListManagement = $requisitionListManagement;
        $this->requisitionListItemLocator = $requisitionListItemLocator;
        $this->requisitionListProduct = $requisitionListProduct;
    }

    /**
     * Set options and save requisition list item.
     *
     * @param \Magento\Framework\DataObject $productData
     * @param array $options
     * @param int $itemId
     * @param int $listId
     * @return \Magento\Framework\Phrase
     */
    public function saveItem(\Magento\Framework\DataObject $productData, array $options, $itemId, $listId)
    {
        $requisitionList = $this->requisitionListRepository->get($listId);
        $itemOptions = $this->optionsBuilder->build($options, $itemId, false);
        $qty = ((int)$productData->getOptions('qty') > 0) ? (int)$productData->getOptions('qty') : 1;
        $item = $this->requisitionListItemLocator->getItem($itemId);
        $item->setQty($qty);
        $item->setOptions($itemOptions);
        $item->setSku($productData->getSku());

        $items = $requisitionList->getItems();

        if ($item->getId()) {
            foreach ($items as $i => $existItem) {
                if ($existItem->getId() == $item->getId()) {
                    $items[$i] = $item;
                }
            }
        } else {
            $items[] = $item;
        }

        $product = $this->requisitionListProduct->getProduct($productData->getSku());
        if ($item->getId()) {
            $message = __('%1 has been updated in your requisition list.', $product->getName());
        } else {
            $message = __(
                'Product %1 has been added to the requisition list %2.',
                $product->getName(),
                $requisitionList->getName()
            );
        }

        $this->requisitionListManagement->setItemsToList($requisitionList, $items);
        $this->requisitionListRepository->save($requisitionList);

        return $message;
    }
}
