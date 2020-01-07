<?php

namespace Magento\CompanyCredit\Model\ResourceModel\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * History collection.
 */
class Collection extends AbstractCollection
{
    /**
     * Standard collection initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\CompanyCredit\Model\History::class,
            \Magento\CompanyCredit\Model\ResourceModel\History::class
        );
    }
}
