<?php

namespace Magento\Company\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Company\Api\Data\StructureInterface;

class Structure extends AbstractModel implements StructureInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'structure';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'structure';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\ResourceModel\Structure::class);
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::STRUCTURE_ID);
    }

    /**
     * Get parent ID
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get entity type
     *
     * @return int|null
     */
    public function getEntityType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Get level
     *
     * @return int|null
     */
    public function getLevel()
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return StructureInterface
     */
    public function setId($id)
    {
        return $this->setData(self::STRUCTURE_ID, $id);
    }

    /**
     * Set parent ID
     *
     * @param int $parentId
     * @return StructureInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return StructureInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Set entity type
     *
     * @param int $entityType
     * @return StructureInterface
     */
    public function setEntityType($entityType)
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return StructureInterface
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * Set position.
     *
     * @param int $position
     * @return StructureInterface
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * Set level
     *
     * @param int $level
     * @return StructureInterface
     */
    public function setLevel($level)
    {
        return $this->setData(self::LEVEL, $level);
    }
}
