<?php
namespace Magento\GiftMessage\Model\ResourceModel\Message;

/**
 * Gift Message collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\GiftMessage\Model\Message::class,
            \Magento\GiftMessage\Model\ResourceModel\Message::class
        );
    }
}
