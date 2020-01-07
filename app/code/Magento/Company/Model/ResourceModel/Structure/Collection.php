<?php
namespace Magento\Company\Model\ResourceModel\Structure;

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
        $this->_init(\Magento\Company\Model\Structure::class, \Magento\Company\Model\ResourceModel\Structure::class);
    }
}
