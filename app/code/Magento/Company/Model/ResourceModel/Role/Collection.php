<?php
namespace Magento\Company\Model\ResourceModel\Role;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Role collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'role_id';

    /**
     * Standard collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\Role::class, \Magento\Company\Model\ResourceModel\Role::class);
    }
}
