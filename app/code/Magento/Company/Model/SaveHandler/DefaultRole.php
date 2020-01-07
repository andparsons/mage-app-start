<?php

namespace Magento\Company\Model\SaveHandler;

use Magento\Company\Model\SaveHandlerInterface;
use Magento\Company\Api\Data\CompanyInterface;

/**
 * Default role creator.
 */
class DefaultRole implements SaveHandlerInterface
{
    /**
     * @var \Magento\Company\Model\RoleFactory
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Model\PermissionManagementInterface
     */
    private $permissionManagement;

    /**
     * @var \Magento\Company\Model\RoleManagement
     */
    private $roleManagement;

    /**
     * @param \Magento\Company\Model\RoleFactory $roleFactory
     * @param \Magento\Company\Api\RoleRepositoryInterface $roleRepository
     * @param \Magento\Company\Model\PermissionManagementInterface $permissionManagement
     * @param \Magento\Company\Model\RoleManagement $roleManagement
     */
    public function __construct(
        \Magento\Company\Model\RoleFactory $roleFactory,
        \Magento\Company\Api\RoleRepositoryInterface $roleRepository,
        \Magento\Company\Model\PermissionManagementInterface $permissionManagement,
        \Magento\Company\Model\RoleManagement $roleManagement
    ) {
        $this->roleFactory = $roleFactory;
        $this->roleRepository = $roleRepository;
        $this->permissionManagement = $permissionManagement;
        $this->roleManagement = $roleManagement;
    }

    /**
     * @inheritdoc
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        if (!$initialCompany->getId()) {
            $role = $this->roleFactory->create();
            $role->setRoleName($this->roleManagement->getCompanyDefaultRoleName());
            $role->setCompanyId($company->getId());
            $role->setPermissions($this->permissionManagement->retrieveDefaultPermissions());
            $this->roleRepository->save($role);
        }
    }
}
