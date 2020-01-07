<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\HierarchyInterface;

/**
 * A DTO for displaying company hierarchy in the WebAPI.
 */
class Hierarchy extends \Magento\Framework\Model\AbstractExtensibleModel implements HierarchyInterface
{
    const STRUCTURE_ID = 'structure_id';
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const STRUCTURE_PARENT_ID = 'structure_parent_id';

    /**
     * @inheritdoc
     */
    public function getStructureId()
    {
        return $this->getData(self::STRUCTURE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStructureId($id)
    {
        return $this->setData(self::STRUCTURE_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getEntityType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setEntityType($type)
    {
        return $this->setData(self::ENTITY_TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getStructureParentId()
    {
        return $this->getData(self::STRUCTURE_PARENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStructureParentId($id)
    {
        return $this->setData(self::STRUCTURE_PARENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\Company\Api\Data\HierarchyExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
