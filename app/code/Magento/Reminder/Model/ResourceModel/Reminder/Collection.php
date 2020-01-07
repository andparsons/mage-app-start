<?php
namespace Magento\Reminder\Model\ResourceModel\Reminder;

/**
 * Reminder data grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Reminder\Model\ResourceModel\Rule\Collection
{
    /**
     * Initialize reminder rule collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();
        return $this;
    }
}
