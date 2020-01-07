<?php
namespace Magento\Framework\EntityManager\Operation;

/**
 * Interface ExtensionInterface
 */
interface ExtensionInterface
{
    /**
     * Perform action on relation/extension attribute
     *
     * @param object $entity
     * @param array $arguments
     * @return object|bool
     */
    public function execute($entity, $arguments = []);
}
