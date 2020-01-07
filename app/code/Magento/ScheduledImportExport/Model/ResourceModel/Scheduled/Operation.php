<?php
namespace Magento\ScheduledImportExport\Model\ResourceModel\Scheduled;

/**
 * Operation resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Operation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource operation model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_scheduled_operations', 'id');

        $this->_useIsObjectNew = true;
    }
}
