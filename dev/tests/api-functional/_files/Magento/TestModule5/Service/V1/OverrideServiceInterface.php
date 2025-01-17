<?php

namespace Magento\TestModule5\Service\V1;

interface OverrideServiceInterface
{
    /**
     * Update existing item.
     *
     * @param string $entityId
     * @param string $name
     * @param bool $orders
     * @return \Magento\TestModule5\Service\V1\Entity\AllSoapAndRest
     */
    public function scalarUpdate($entityId, $name, $orders);
}
