<?php

namespace Magento\TestModule5\Service\V1;

use Magento\TestModule5\Service\V1\Entity\AllSoapAndRestFactory;

class OverrideService implements OverrideServiceInterface
{
    /**
     * @var AllSoapAndRestFactory
     */
    protected $factory;

    /**
     * @param AllSoapAndRestFactory $factory
     */
    public function __construct(AllSoapAndRestFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function scalarUpdate($entityId, $name, $hasOrders)
    {
        return $this->factory->create()
            ->setEntityId($entityId)
            ->setName($name)
            ->setHasOrders($hasOrders);
    }
}
