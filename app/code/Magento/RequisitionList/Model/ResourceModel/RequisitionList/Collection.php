<?php
namespace Magento\RequisitionList\Model\ResourceModel\RequisitionList;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Requisition List collection
 */
class Collection extends AbstractCollection
{
    /**
     * Standard collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\RequisitionList\Model\RequisitionList::class,
            \Magento\RequisitionList\Model\ResourceModel\RequisitionList::class
        );
    }
}
