<?php
namespace Magento\Company\Model\ResourceModel\Team;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Company collection
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
        $this->_init(\Magento\Company\Model\Team::class, \Magento\Company\Model\ResourceModel\Team::class);
    }
}
