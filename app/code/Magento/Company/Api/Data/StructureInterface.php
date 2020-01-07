<?php
namespace Magento\Company\Api\Data;

/**
 * Structure interface
 *
 * @api
 * @since 100.0.0
 */
interface StructureInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STRUCTURE_ID  = 'structure_id';
    const PARENT_ID     = 'parent_id';
    const ENTITY_ID     = 'entity_id';
    const ENTITY_TYPE   = 'entity_type';
    const PATH          = 'path';
    const POSITION      = 'position';
    const LEVEL         = 'level';
    /**#@-*/

    /**#@+
     * Constants for entity types
     */
    const TYPE_CUSTOMER = 0;
    const TYPE_TEAM     = 1;
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get parent ID
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get entity type
     *
     * @return int|null
     */
    public function getEntityType();

    /**
     * Get path
     *
     * @return string|null
     */
    public function getPath();

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Get level
     *
     * @return int|null
     */
    public function getLevel();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setId($id);

    /**
     * Set parent ID
     *
     * @param int $parentId
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setParentId($parentId);

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setEntityId($entityId);

    /**
     * Set entity type
     *
     * @param int $entityType
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setEntityType($entityType);

    /**
     * Set path
     *
     * @param string $path
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setPath($path);

    /**
     * Set position.
     *
     * @param int $position
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setPosition($position);

    /**
     * Set level
     *
     * @param int $level
     * @return \Magento\Company\Api\Data\StructureInterface
     */
    public function setLevel($level);
}
