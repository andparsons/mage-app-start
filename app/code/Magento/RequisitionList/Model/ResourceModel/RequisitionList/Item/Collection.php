<?php
namespace Magento\RequisitionList\Model\ResourceModel\RequisitionList\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Requisition List Item collection
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\RequisitionList\Model\RequisitionListItem::class,
            \Magento\RequisitionList\Model\ResourceModel\RequisitionListItem::class
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }
}
