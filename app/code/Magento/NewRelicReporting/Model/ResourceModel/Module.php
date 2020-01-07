<?php

namespace Magento\NewRelicReporting\Model\ResourceModel;

class Module extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize module status resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('reporting_module_status', 'entity_id');
    }
}
