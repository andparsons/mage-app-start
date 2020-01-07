<?php

namespace Magento\RequisitionList\Model\RequisitionList\Order;

/**
 * Processes order information, add requisition list items to a requisition list.
 */
class Converter
{
    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface
     */
    private $requisitionListManagement;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter
     */
    private $requisitionListItemConverter;

    /**
     * @var \Magento\RequisitionList\Model\ProductSkuLocator
     */
    private $productSkuLocator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $productSkuById;

    /**
     * @param \Magento\RequisitionList\Api\RequisitionListRepositoryInterface $requisitionListRepository
     * @param \Magento\RequisitionList\Api\RequisitionListManagementInterface $requisitionListManagement
     * @param \Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter $requisitionListItemConverter
     * @param \Magento\RequisitionList\Model\ProductSkuLocator $productSkuLocator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\RequisitionList\Api\RequisitionListRepositoryInterface $requisitionListRepository,
        \Magento\RequisitionList\Api\RequisitionListManagementInterface $requisitionListManagement,
        \Magento\RequisitionList\Model\RequisitionListItem\OrderItem\Converter $requisitionListItemConverter,
        \Magento\RequisitionList\Model\ProductSkuLocator $productSkuLocator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->requisitionListRepository = $requisitionListRepository;
        $this->requisitionListManagement = $requisitionListManagement;
        $this->requisitionListItemConverter = $requisitionListItemConverter;
        $this->productSkuLocator = $productSkuLocator;
        $this->dateTime = $dateTime;
    }

    /**
     * Processes order information, adds requisition list items to a requisition list.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\RequisitionList\Api\Data\RequisitionListInterface $requisitionList
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addItems(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\RequisitionList\Api\Data\RequisitionListInterface $requisitionList
    ) {
        $requisitionListItems = [];
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItemId()) {
                continue;
            }

            $sku = $this->getSku($orderItems, $orderItem->getProductId());
            $requisitionListItem = $this->requisitionListItemConverter->convert($orderItem, $sku);
            $this->requisitionListManagement->addItemToList($requisitionList, $requisitionListItem);
            $requisitionListItems[] = $requisitionListItem;
        }

        $this->requisitionListRepository->save($requisitionList);

        return $requisitionListItems;
    }

    /**
     * Retrieve order products skus.
     *
     * @param array $orderItems
     * @param int $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSku(array $orderItems, $productId)
    {
        if (!isset($this->productSkuById[$productId])) {
            $productIds = array_map(
                function ($orderItem) {
                    return $orderItem->getProductId();
                },
                $orderItems
            );
            $this->productSkuById = $this->productSkuLocator->getProductSkus($productIds);
        }
        if (!isset($this->productSkuById[$productId])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested product doesn\'t exist'));
        }
        return $this->productSkuById[$productId];
    }
}
