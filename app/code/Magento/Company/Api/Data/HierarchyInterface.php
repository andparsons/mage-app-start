<?php
namespace Magento\Company\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Company hierarchy DTO interface for WebAPI.
 *
 * @api
 * @since 100.0.0
 */
interface HierarchyInterface extends ExtensibleDataInterface
{
    const TYPE_CUSTOMER = 'customer';
    const TYPE_TEAM = 'team';

    /**
     * Get structure ID.
     *
     * @return int|null
     */
    public function getStructureId();

    /**
     * Set structure ID.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\HierarchyInterface
     */
    public function setStructureId($id);

    /**
     * Get entity ID.
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity ID.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\HierarchyInterface
     */
    public function setEntityId($id);

    /**
     * Get entity type.
     *
     * @return string|null
     */
    public function getEntityType();

    /**
     * Set entity type.
     *
     * @param string $type
     * @return \Magento\Company\Api\Data\HierarchyInterface
     */
    public function setEntityType($type);

    /**
     * Get structure parent ID.
     *
     * @return int|null
     */
    public function getStructureParentId();

    /**
     * Set structure parent ID.
     *
     * @param int $id
     * @return \Magento\Company\Api\Data\HierarchyInterface
     */
    public function setStructureParentId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Company\Api\Data\HierarchyExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Company\Api\Data\HierarchyExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Company\Api\Data\HierarchyExtensionInterface $extensionAttributes
    );
}
