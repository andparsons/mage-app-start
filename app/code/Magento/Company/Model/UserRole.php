<?php

namespace Magento\Company\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class UserRole.
 */
class UserRole extends AbstractModel
{
    /**
     * Cache tag.
     */
    const CACHE_TAG = 'user_role';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'user_role';

    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Company\Model\ResourceModel\UserRole::class);
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData('user_role_id', $id);
    }

    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('user_role_id');
    }

    /**
     * Set user id.
     *
     * @param int $id
     * @return $this
     */
    public function setUserId($id)
    {
        return $this->setData('user_id', $id);
    }

    /**
     * Get user id.
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->getData('user_id');
    }

    /**
     * Set role id.
     *
     * @param int $id
     * @return $this
     */
    public function setRoleId($id)
    {
        return $this->setData('role_id', $id);
    }

    /**
     * Get role id.
     *
     * @return int|null
     */
    public function getRoleId()
    {
        return $this->getData('role_id');
    }
}
