<?php

namespace Magento\Sales\Model\ResourceModel\Order;

/**
 * Class CollectionFactoryInterface
 */
interface CollectionFactoryInterface
{
    /**
     * Create class instance with specified parameters
     *
     * @param int $customerId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function create($customerId = null);
}
