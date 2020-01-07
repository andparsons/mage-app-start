<?php
namespace Magento\Company\Model\Authorization;

/**
 * Class PermissionProvider.
 */
class PermissionProvider
{
    /**
     * \Magento\Company\Model\ResourceModel\Permission\Collection
     */
    private $permissionCollection;

    /**
     * @var \Magento\Company\Model\ResourcePool
     */
    private $resourcePool;

    /**
     * PermissionProvider constructor.
     *
     * @param \Magento\Company\Model\ResourceModel\Permission\Collection $permissionCollection
     * @param \Magento\Company\Model\ResourcePool $resourcePool
     */
    public function __construct(
        \Magento\Company\Model\ResourceModel\Permission\Collection $permissionCollection,
        \Magento\Company\Model\ResourcePool $resourcePool
    ) {
        $this->permissionCollection = $permissionCollection;
        $this->resourcePool = $resourcePool;
    }

    /**
     * Retrieve permissions hash array.
     *
     * @param int $roleId
     * @return array
     */
    public function retrieveRolePermissions($roleId)
    {
        return $this->permissionCollection
            ->addFieldToFilter('role_id', ['eq' => $roleId])
            ->toOptionHash('resource_id', 'permission');
    }

    /**
     * Retrieve default role permissions.
     *
     * @return array
     */
    public function retrieveDefaultPermissions()
    {
        $permissions = [];
        $resources = $this->resourcePool->getDefaultResources();
        foreach ($resources as $resource) {
            $permissions[$resource] = 'allow';
        }

        return $permissions;
    }
}
