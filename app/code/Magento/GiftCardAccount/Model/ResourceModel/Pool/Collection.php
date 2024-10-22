<?php
namespace Magento\GiftCardAccount\Model\ResourceModel\Pool;

/**
 * GiftCardAccount Pool Resource Model Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\GiftCardAccount\Model\Pool::class,
            \Magento\GiftCardAccount\Model\ResourceModel\Pool::class
        );
    }
}
