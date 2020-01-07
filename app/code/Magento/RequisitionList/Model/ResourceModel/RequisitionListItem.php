<?php
namespace Magento\RequisitionList\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Requisition List resource model
 */
class RequisitionListItem extends AbstractDb
{
    /**
     * Requisition List item table
     *
     * @var string
     */
    private $requisitionListItemTable = 'requisition_list_item';

    /**
     * @var array
     */
    protected $_serializableFields = [
        \Magento\RequisitionList\Model\RequisitionListItem::OPTIONS => [null, []]
    ];

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->requisitionListItemTable, 'item_id');
    }
}
