<?php
namespace Magento\CustomerCustomAttributes\Model\Sales\Order;

/**
 * Customer Order Address model
 *
 * @method \Magento\CustomerCustomAttributes\Model\Sales\Order\Address setEntityId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Address extends \Magento\CustomerCustomAttributes\Model\Sales\Address\AbstractAddress
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\CustomerCustomAttributes\Model\ResourceModel\Sales\Order\Address::class);
    }
}
