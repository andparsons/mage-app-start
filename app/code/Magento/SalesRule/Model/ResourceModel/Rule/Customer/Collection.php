<?php
namespace Magento\SalesRule\Model\ResourceModel\Rule\Customer;

/**
 * SalesRule Model Resource Rule Customer_Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Collection constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\SalesRule\Model\Rule\Customer::class,
            \Magento\SalesRule\Model\ResourceModel\Rule\Customer::class
        );
    }
}
