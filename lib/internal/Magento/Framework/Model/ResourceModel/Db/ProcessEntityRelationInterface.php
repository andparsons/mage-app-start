<?php

namespace Magento\Framework\Model\ResourceModel\Db;

/**
 * Class ProcessEntityRelationInterface
 */
interface ProcessEntityRelationInterface
{
    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity);
}
