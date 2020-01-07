<?php
namespace Magento\Company\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Order company extension attributes resource model.
 */
class Order extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('company_order_entity', \Magento\Company\Api\Data\CompanyOrderInterface::ENTITY_ID);
    }
}
