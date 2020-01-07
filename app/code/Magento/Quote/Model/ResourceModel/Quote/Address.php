<?php
namespace Magento\Quote\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

/**
 * Quote address resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Address extends AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_address', 'address_id');
    }
}
