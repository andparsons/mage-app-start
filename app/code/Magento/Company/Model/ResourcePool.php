<?php

namespace Magento\Company\Model;

/**
 * Resources pool.
 */
class ResourcePool
{
    /**
     * @var array
     */
    private $resources;

    /**
     * @param array $resources
     */
    public function __construct(
        $resources = []
    ) {
        $this->resources = $resources;
    }

    /**
     * Get default resources.
     *
     * @return array
     */
    public function getDefaultResources()
    {
        return $this->resources;
    }
}
