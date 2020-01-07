<?php

namespace Magento\CompanyCredit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * History mysql resource.
 */
class History extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('company_credit_history', 'entity_id');
    }
}
