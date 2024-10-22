<?php
namespace Magento\Staging\Model\Operation\Update;

/**
 * Interface \Magento\Staging\Model\Operation\Update\UpdateProcessorInterface
 *
 */
interface UpdateProcessorInterface
{
    /**
     * Process update
     *
     * @param object $entity
     * @param int $versionId
     * @param int $rollbackId
     * @return object
     */
    public function process($entity, $versionId, $rollbackId = null);
}
