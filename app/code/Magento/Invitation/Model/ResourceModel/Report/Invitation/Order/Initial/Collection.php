<?php
namespace Magento\Invitation\Model\ResourceModel\Report\Invitation\Order\Initial;

/**
 * Reports invitation order report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * @var string
     */
    protected $_reportCollection = \Magento\Invitation\Model\ResourceModel\Report\Invitation\Order\Collection::class;
}
