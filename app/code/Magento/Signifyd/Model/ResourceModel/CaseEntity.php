<?php
namespace Magento\Signifyd\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Implementation of case resource model
 */
class CaseEntity extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('signifyd_case', 'entity_id');
    }
}
