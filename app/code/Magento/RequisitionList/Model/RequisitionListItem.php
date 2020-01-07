<?php

namespace Magento\RequisitionList\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;
use Magento\Framework\Model\AbstractExtensibleModel as ExtensibleModel;

/**
 * Requisition List Item Model.
 */
class RequisitionListItem extends ExtensibleModel implements RequisitionListItemInterface
{
    /**
     * Initialize resource.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\RequisitionList\Model\ResourceModel\RequisitionListItem::class);
    }

    /**
     * @inheritdoc
     */
    public function setId($requisitionListItemId)
    {
        return $this->setData(RequisitionListItemInterface::REQUISITION_LIST_ITEM_ID, $requisitionListItemId);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(RequisitionListItemInterface::REQUISITION_LIST_ITEM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(RequisitionListItemInterface::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(RequisitionListItemInterface::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setRequisitionListId($requisitionListId)
    {
        return $this->setData(RequisitionListItemInterface::REQUISITION_LIST_ID, $requisitionListId);
    }

    /**
     * @inheritdoc
     */
    public function getRequisitionListId()
    {
        return $this->getData(RequisitionListItemInterface::REQUISITION_LIST_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQty($qty)
    {
        return $this->setData(RequisitionListItemInterface::QTY, $qty);
    }

    /**
     * @inheritdoc
     */
    public function getQty()
    {
        return $this->getData(RequisitionListItemInterface::QTY);
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        return $this->setData(RequisitionListItemInterface::OPTIONS, $options);
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->getData(RequisitionListItemInterface::OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(RequisitionListItemInterface::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->getData(RequisitionListItemInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAddedAt($date)
    {
        return $this->setData(RequisitionListItemInterface::ADDED_AT, $date);
    }

    /**
     * @inheritdoc
     */
    public function getAddedAt()
    {
        return $this->getData(RequisitionListItemInterface::ADDED_AT);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\RequisitionList\Api\Data\RequisitionListItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
