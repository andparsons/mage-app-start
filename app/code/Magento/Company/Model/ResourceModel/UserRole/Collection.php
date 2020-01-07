<?php
namespace Magento\Company\Model\ResourceModel\UserRole;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Permission collection.
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'user_role_id';

    /**
     * Standard collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\UserRole::class, \Magento\Company\Model\ResourceModel\UserRole::class);
    }
}
