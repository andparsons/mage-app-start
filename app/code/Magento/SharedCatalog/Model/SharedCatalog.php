<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * SharedCatalog Model.
 */
class SharedCatalog extends \Magento\Framework\Model\AbstractModel implements SharedCatalogInterface
{
    const CATALOG_PUBLIC = 'Public';

    const CATALOG_CUSTOM = 'Custom';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\SharedCatalog\Model\ResourceModel\SharedCatalog::class);
    }

    /**
     * Prepare Shared Catalog types.
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        return [self::TYPE_CUSTOM => __('Custom'), self::TYPE_PUBLIC => __('Public')];
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->getData(self::SHARED_CATALOG_ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(self::SHARED_CATALOG_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupId(): ?int
    {
        return $this->getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupId($id)
    {
        return $this->setData(self::CUSTOMER_GROUP_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getType(): ?int
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedBy(): ?int
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): ?int
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getTaxClassId(): ?int
    {
        return $this->getData(self::TAX_CLASS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTaxClassId($taxClassId)
    {
        return $this->setData(self::TAX_CLASS_ID, $taxClassId);
    }
}
