<?php
namespace Magento\Quote\Model\ResourceModel\Quote\Item;

/**
 * Item option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote_item_option', 'option_id');
    }
}
