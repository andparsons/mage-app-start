<?php
namespace Magento\RequisitionList\Block\Requisition\View\Items;

use Magento\Framework\View\Element\Template\Context;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\RequisitionList\Model\RequisitionListItem\Validation;

/**
 * Grid of requisition list items.
 *
 * @api
 * @since 100.0.0
 */
class Grid extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Validation
     */
    private $validation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\ItemSelector
     */
    private $itemSelector;

    /**
     * @var int
     */
    private $itemErrorCount = 0;

    /**
     * @var array
     */
    private $errorsByItemId = [];

    /**
     * @param Context $context
     * @param Validation $validation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\RequisitionList\Model\RequisitionList\ItemSelector $itemsSelector
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        Validation $validation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\RequisitionList\Model\RequisitionList\ItemSelector $itemSelector,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->validation = $validation;
        $this->storeManager = $storeManager;
        $this->itemSelector = $itemSelector;
    }

    /**
     * Get count of items with errors.
     *
     * @return int
     */
    public function getItemErrorCount()
    {
        return $this->itemErrorCount;
    }

    /**
     * Get list of items that are included in requisition list.
     *
     * @return RequisitionListItemInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRequisitionListItems()
    {
        $requisitionId = $this->getRequest()->getParam('requisition_id');
        if ($requisitionId === null) {
            return null;
        }

        $items = $this->itemSelector->selectAllItemsFromRequisitionList(
            $requisitionId,
            $this->storeManager->getWebsite()->getId()
        );
        foreach ($items as $item) {
            $this->checkForItemError($item);
        }
        uasort($items, function (RequisitionListItemInterface $firstItem, RequisitionListItemInterface $secondItem) {
            $isFirstItemError = !empty($this->errorsByItemId[$firstItem->getId()]);
            $isSecondItemError = !empty($this->errorsByItemId[$secondItem->getId()]);

            return (int)$isSecondItemError - (int)$isFirstItemError;
        });

        return $items;
    }

    /**
     * Check if product is enabled and its quantity is available.
     *
     * @param RequisitionListItemInterface $item
     * @return bool
     */
    private function checkForItemError(RequisitionListItemInterface $item)
    {
        try {
            $errors = $this->validation->validate($item);

            if (count($errors)) {
                $this->errorsByItemId[$item->getId()] = $errors;
                $this->itemErrorCount++;
            }
            $isItemHasErrors = (count($errors) > 0);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->itemErrorCount++;
            $this->errorsByItemId[$item->getId()] = [__('The SKU was not found in the catalog.')];
            $isItemHasErrors = true;
        }

        return $isItemHasErrors;
    }

    /**
     * Get errors for requisition list item.
     *
     * @param RequisitionListItemInterface $item
     * @return array
     */
    public function getItemErrors(RequisitionListItemInterface $item)
    {
        return !empty($this->errorsByItemId[$item->getId()]) ? $this->errorsByItemId[$item->getId()] : [];
    }
}
