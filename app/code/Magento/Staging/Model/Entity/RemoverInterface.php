<?php
namespace Magento\Staging\Model\Entity;

/**
 * Interface RemoverInterface
 */
interface RemoverInterface
{
    /**
     * @param object $entity
     * @param string $versionId
     * @return boolean
     */
    public function deleteEntity($entity, $versionId);
}
