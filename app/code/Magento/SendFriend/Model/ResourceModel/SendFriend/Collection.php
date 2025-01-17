<?php
namespace Magento\SendFriend\Model\ResourceModel\SendFriend;

/**
 * SendFriend log resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\SendFriend\Model\SendFriend::class,
            \Magento\SendFriend\Model\ResourceModel\SendFriend::class
        );
    }
}
