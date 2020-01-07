<?php
namespace Magento\Sales\Model\ResourceModel\Report\Order\Updatedat;

/**
 * Report order updated_at collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Order\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'sales_order_aggregated_updated';
}
