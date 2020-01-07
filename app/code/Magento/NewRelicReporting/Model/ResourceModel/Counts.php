<?php

namespace Magento\NewRelicReporting\Model\ResourceModel;

class Counts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize users resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('reporting_counts', 'entity_id');
    }
}
