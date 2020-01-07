<?php
namespace Magento\SharedCatalog\Api\Data;

/**
 * SharedCatalogInterface interface.
 * @api
 * @since 100.0.0
 */
interface SharedCatalogInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const SHARED_CATALOG_ID = 'entity_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const TYPE = 'type';
    const CREATED_AT = 'created_at';
    const CREATED_BY = 'created_by';
    const STORE_ID = 'store_id';
    const TAX_CLASS_ID = 'tax_class_id';
    /**#@-*/

    /**
     * Shared Catalog type definition.
     */
    const TYPE_PUBLIC = 1;
    const TYPE_CUSTOM = 0;

    /**
     * Shared catalog ID parameter name for URLs.
     */
    const SHARED_CATALOG_ID_URL_PARAM = 'shared_catalog_id';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setId($id);

    /**
     * Get Shared Catalog name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set Shared Catalog name.
     *
     * @param string $name
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setName($name);

    /**
     * Get Shared Catalog description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set Shared Catalog description.
     *
     * @param string $description
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setDescription($description);

    /**
     * Get Customer Group Id.
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set Customer Group ID.
     *
     * @param int $customerGroupId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Get Shared Catalog type.
     *
     * @return int
     */
    public function getType();

    /**
     * Set Shared Catalog type.
     *
     * @param int $type
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setType($type);

    /**
     * Get created time for Shared Catalog.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created time for Shared Catalog.
     *
     * @param string $createdAt
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get admin id for Shared Catalog.
     *
     * @return int
     */
    public function getCreatedBy();

    /**
     * Set admin id for Shared Catalog.
     *
     * @param int $createdBy
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setCreatedBy($createdBy);

    /**
     * Get store id for Shared Catalog.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store id for Shared Catalog.
     *
     * @param int $storeId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setStoreId($storeId);

    /**
     * Get tax class id.
     *
     * @return int
     */
    public function getTaxClassId();

    /**
     * Set tax class id.
     *
     * @param int $taxClassId
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    public function setTaxClassId($taxClassId);
}
