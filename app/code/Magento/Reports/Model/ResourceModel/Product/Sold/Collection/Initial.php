<?php

/**
 * Report Reviews collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Product\Sold\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Initial extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     *
     * @var string
     */
    protected $_reportCollection = \Magento\Reports\Model\ResourceModel\Product\Sold\Collection::class;
}
