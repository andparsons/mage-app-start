<?php
namespace Magento\SalesRule\Model\ResourceModel\Report\Updatedat;

/**
 * Sales report coupons collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\SalesRule\Model\ResourceModel\Report\Collection
{
    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'salesrule_coupon_aggregated_updated';
}
