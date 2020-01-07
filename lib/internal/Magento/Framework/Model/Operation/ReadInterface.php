<?php

namespace Magento\Framework\Model\Operation;

/**
 * Interface ReadInterface
 */
interface ReadInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @param string $identifier
     * @return object
     */
    public function execute($entityType, $entity, $identifier);
}
