<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\RoleInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Company\Model\ResourceModel\Permission\CollectionFactory as PermissionCollectionFactory;

/**
 * Role data transfer object.
 */
class Role extends AbstractExtensibleModel implements RoleInterface
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'company_role';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'company_role';

    /**
     * Role permissions.
     *
     * @var \Magento\Company\Api\Data\PermissionInterface[]
     */
    private $permissions = [];

    /**
     * Initialize resource model.
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\Magento\Company\Model\ResourceModel\Role::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ROLE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoleName($name)
    {
        return $this->setData(self::ROLE_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompanyId($id)
    {
        return $this->setData(self::COMPANY_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleName()
    {
        return $this->getData(self::ROLE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompanyId()
    {
        return $this->getData(self::COMPANY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        if (!$this->_getExtensionAttributes()) {
            $this->setExtensionAttributes(
                $this->extensionAttributesFactory->create(get_class($this))
            );
        }
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(\Magento\Company\Api\Data\RoleExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
