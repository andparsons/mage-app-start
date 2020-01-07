<?php

namespace Magento\Company\Model\Role;

/**
 * Validator for Role data.
 */
class Validator
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Api\AclInterface
     */
    private $userRoleManagement;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface
     */
    private $roleManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param \Magento\Company\Api\RoleRepositoryInterface $roleRepository
     * @param \Magento\Company\Api\AclInterface $userRoleManagement
     * @param \Magento\Company\Api\RoleManagementInterface $roleManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Company\Api\RoleRepositoryInterface $roleRepository,
        \Magento\Company\Api\AclInterface $userRoleManagement,
        \Magento\Company\Api\RoleManagementInterface $roleManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->companyRepository = $companyRepository;
        $this->roleRepository = $roleRepository;
        $this->userRoleManagement = $userRoleManagement;
        $this->roleManagement = $roleManagement;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Merges requested role object onto the original role and validate role data.
     *
     * @param \Magento\Company\Api\Data\RoleInterface $requestedRole
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Company\Api\Data\RoleInterface
     */
    public function retrieveRole(\Magento\Company\Api\Data\RoleInterface $requestedRole)
    {
        if ($requestedRole->getId()) {
            $requestedCompanyId = $requestedRole->getCompanyId();
            $originalRole = $this->roleRepository->get($requestedRole->getId());
            $this->dataObjectHelper->mergeDataObjects(
                \Magento\Company\Api\Data\RoleInterface::class,
                $originalRole,
                $requestedRole
            );
            $role = $originalRole;
            if ($requestedCompanyId && $role->getCompanyId() != $requestedCompanyId) {
                throw new \Magento\Framework\Exception\InputException(
                    __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        ['fieldName' => 'company_id', 'value' => $requestedCompanyId]
                    )
                );
            }
        } else {
            $role = $requestedRole;
        }
        if (!$role->getRoleName()) {
            throw new \Magento\Framework\Exception\InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'role_name'])
            );
        }
        if (!$role->getId() && !$role->getCompanyId()) {
            throw new \Magento\Framework\Exception\InputException(
                __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'company_id'])
            );
        }
        try {
            $this->companyRepository->get($role->getCompanyId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    ['fieldName' => 'company_id', 'fieldValue' => $role->getCompanyId()]
                )
            );
        }

        return $role;
    }

    /**
     * Validate permissions before saving the role.
     *
     * @param \Magento\Company\Api\Data\PermissionInterface[] $permissions
     * @param array $allowedResources
     * @throws \Magento\Framework\Exception\InputException
     * @return void
     */
    public function validatePermissions(array $permissions, array $allowedResources)
    {
        $allResources = [];
        foreach ($permissions as $permission) {
            $allResources[] = $permission->getResourceId();
        }
        $invalidResources = array_diff($allowedResources, $allResources);
        if ($invalidResources) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'resource_id', 'value' => $invalidResources[0]]
                )
            );
        }
    }

    /**
     * Validates the role before delete.
     *
     * @param \Magento\Company\Api\Data\RoleInterface $role
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @return void
     */
    public function validateRoleBeforeDelete(\Magento\Company\Api\Data\RoleInterface $role)
    {
        $roleUsers = $this->userRoleManagement->getUsersCountByRoleId($role->getId());
        if ($roleUsers) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __(
                    'This role cannot be deleted because users are assigned to it. '
                    . 'Reassign the users to another role to continue.'
                )
            );
        }
        $roles = $this->roleManagement->getRolesByCompanyId($role->getCompanyId(), false);
        if (count($roles) <= 1) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __(
                    'You cannot delete a role when it is the only role in the company. '
                    . 'You must create another role before deleting this role.'
                )
            );
        }
    }

    /**
     * Check if Role exist.
     *
     * @param \Magento\Company\Api\Data\RoleInterface $role
     * @param int $roleId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function checkRoleExist(
        \Magento\Company\Api\Data\RoleInterface $role,
        $roleId
    ) {
        if (!$role->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('No such entity with %fieldName = %fieldValue', ['fieldName' => 'roleId', 'fieldValue' => $roleId])
            );
        }
    }
}
