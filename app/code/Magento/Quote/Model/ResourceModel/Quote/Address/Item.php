<?php
namespace Magento\Quote\Model\ResourceModel\Quote\Address;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Quote address item resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Item extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_address_item', 'address_item_id');
    }
}
