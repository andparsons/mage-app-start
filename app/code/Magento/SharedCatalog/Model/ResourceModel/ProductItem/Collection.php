<?php
namespace Magento\SharedCatalog\Model\ResourceModel\ProductItem;

/**
 * ProductItem collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\SharedCatalog\Model\ProductItem::class,
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem::class
        );
    }
}
