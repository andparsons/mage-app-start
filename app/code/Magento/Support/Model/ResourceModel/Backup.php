<?php
namespace Magento\Support\Model\ResourceModel;

/**
 * Base resource model for backups
 */
class Backup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('support_backup', 'backup_id');
    }
}
