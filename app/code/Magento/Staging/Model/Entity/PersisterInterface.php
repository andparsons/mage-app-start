<?php
namespace Magento\Staging\Model\Entity;

/**
 * Interface PersisterInterface
 */
interface PersisterInterface
{
    /**
     * @param object $entity
     * @param string $versionId
     * @return bool mixed
     */
    public function saveEntity($entity, $versionId);
}
