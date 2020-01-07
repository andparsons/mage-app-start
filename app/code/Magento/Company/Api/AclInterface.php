<?php

namespace Magento\Company\Api;

/**
 * Access control list interface.
 *
 * @api
 * @since 100.0.0
 */
interface AclInterface
{
    /**
     * Change a role for a company user.
     *
     * @param int $userId
     * @param \Magento\Company\Api\Data\RoleInterface[] $roles
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return bool
     */
    public function assignRoles($userId, array $roles);

    /**
     * Get the list of roles by user id.
     *
     * @param int $userId
     * @return \Magento\Company\Api\Data\RoleInterface[]
     */
    public function getRolesByUserId($userId);

    /**
     * View the list of company users assigned to a specified role.
     *
     * @param int $roleId
     * @return \Magento\Customer\Api\Data\CustomerInterface[]
     */
    public function getUsersByRoleId($roleId);

    /**
     * Get users count by role id.
     *
     * @param int $roleId
     * @return int
     */
    public function getUsersCountByRoleId($roleId);

    /**
     * Assign default company role for a user.
     *
     * @param int $userId
     * @param int $companyId
     * @return void
     */
    public function assignUserDefaultRole($userId, $companyId);

    /**
     * Delete role for a user.
     *
     * @param int $userId
     * @return void
     */
    public function deleteRoles($userId);
}
