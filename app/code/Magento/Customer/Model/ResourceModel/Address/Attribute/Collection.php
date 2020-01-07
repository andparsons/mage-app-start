<?php

/**
 * Customer Address EAV additional attribute resource collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Model\ResourceModel\Address\Attribute;

class Collection extends \Magento\Customer\Model\ResourceModel\Attribute\Collection
{
    /**
     * Default attribute entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = 'customer_address';
}
