<?php
namespace Magento\AdminGws\Model\Observer;

use Magento\AdminGws\Model\Role;

/**
 * Abstract adminGws observer
 *
 */
class AbstractObserver
{
    /**
     * @var Role
     */
    protected $_role;

    /**
     * Initialize helper
     *
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->_role = $role;
    }
}
