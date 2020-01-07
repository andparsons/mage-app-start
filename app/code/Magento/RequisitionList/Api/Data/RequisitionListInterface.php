<?php
namespace Magento\RequisitionList\Api\Data;

/**
 * Interface RequisitionListInterface
 *
 * @api
 * @since 100.0.0
 */
interface RequisitionListInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const REQUISITION_LIST_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const ITEMS = 'items';
    const UPDATED_AT = 'updated_at';

    /**
     * Set Requisition List ID
     *
     * @param int $requisitionListId
     * @return $this
     */
    public function setId($requisitionListId);

    /**
     * Get Requisition List ID
     *
     * @return int
     */
    public function getId();

    /**
     * Set Customer ID
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get Customer ID
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set Requisition List Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get Requisition List Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get Requisition List Update Time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Sets Requisition List Update Time
     *
     * @param string $time
     * @return $this
     */
    public function setUpdatedAt($time);

    /**
     * Set Requisition List Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get Requisition List Description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set Requisition List Items
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * Get Requisition List Items
     *
     * @return \Magento\RequisitionList\Api\Data\RequisitionListItemInterface[]
     */
    public function getItems();

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\RequisitionList\Api\Data\RequisitionListExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\RequisitionList\Api\Data\RequisitionListExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\RequisitionList\Api\Data\RequisitionListExtensionInterface $extensionAttributes
    );
}
