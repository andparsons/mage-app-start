<?php

/**
 * Reports Viewed Product Index Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Product\Index\Viewed;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Index\Collection\AbstractCollection
{
    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    protected function _getTableName()
    {
        return $this->getTable('report_viewed_product_index');
    }
}
