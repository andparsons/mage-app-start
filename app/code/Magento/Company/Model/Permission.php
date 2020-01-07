<?php

namespace Magento\Company\Model;

use Magento\Company\Api\Data\PermissionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Permission.
 */
class Permission extends AbstractModel implements PermissionInterface
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'company_permission';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'company_permission';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\ResourceModel\Permission::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::PERMISSION_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::PERMISSION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRoleId($id)
    {
        return $this->setData(self::ROLE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceId()
    {
        return $this->getData(self::RESOURCE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceId($id)
    {
        return $this->setData(self::RESOURCE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission()
    {
        return $this->getData(self::PERMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPermission($permission)
    {
        return $this->setData(self::PERMISSION, $permission);
    }
}
