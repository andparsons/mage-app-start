<?php
namespace Magento\Ui\Model\ResourceModel\Bookmark;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Bookmark Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Ui\Model\Bookmark::class, \Magento\Ui\Model\ResourceModel\Bookmark::class);
    }
}
