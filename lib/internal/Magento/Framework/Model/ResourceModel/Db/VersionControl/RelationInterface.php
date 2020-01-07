<?php
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

/**
 * Interface RelationInterface
 */
interface RelationInterface
{
    /**
     * Process object relations
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    public function processRelation(\Magento\Framework\Model\AbstractModel $object);
}
