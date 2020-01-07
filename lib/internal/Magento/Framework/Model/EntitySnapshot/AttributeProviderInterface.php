<?php

namespace Magento\Framework\Model\EntitySnapshot;

/**
 * Interface AttributeProviderInterface
 */
interface AttributeProviderInterface
{
    /**
     * @param string $entityType
     * @return array
     */
    public function getAttributes($entityType);
}
