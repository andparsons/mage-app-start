<?php

/**
 * Report event type resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Event;

/**
 * @api
 * @since 100.0.2
 */
class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Main table initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('report_event_types', 'event_type_id');
    }
}
