<?php
namespace Magento\Company\Model\ResourceModel\Permission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Permission collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'permission_id';

    /**
     * Custom option hash for permissions.
     *
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    public function toOptionHash($valueField = 'resource_id', $labelField = 'permission')
    {
        return $this->_toOptionHash($valueField, $labelField);
    }

    /**
     * Standard collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\Permission::class, \Magento\Company\Model\ResourceModel\Permission::class);
    }
}
