<?php

namespace Magento\NewRelicReporting\Model\ResourceModel\Module;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize module status resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\NewRelicReporting\Model\Module::class,
            \Magento\NewRelicReporting\Model\ResourceModel\Module::class
        );
    }
}
