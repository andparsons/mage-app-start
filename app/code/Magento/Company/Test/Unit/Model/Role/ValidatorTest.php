<?php

namespace Magento\Company\Test\Unit\Model\Role;

/**
 * Test for Magento\Company\Model\Role\Validator class.
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleManagement;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Company\Model\Role\Validator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->roleRepository = $this->getMockBuilder(\Magento\Company\Api\RoleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userRoleManagement = $this->getMockBuilder(\Magento\Company\Api\AclInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->roleManagement = $this->getMockBuilder(\Magento\Company\Api\RoleManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dataObjectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Role\Validator::class,
            [
                'companyRepository' => $this->companyRepository,
                'roleRepository' => $this->roleRepository,
                'userRoleManagement' => $this->userRoleManagement,
                'roleManagement' => $this->roleManagement,
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );
    }

    /**
     * Test retrieveRole method.
     *
     * @return void
     */
    public function testRetrieveRole()
    {
        $roleId = 1;
        $requestedRole = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $originalRole = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requestedRole->expects($this->atLeastOnce())->method('getId')->willReturn($roleId);
        $requestedRole->expects($this->once())->method('getCompanyId')->willReturn(1);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($originalRole);
        $this->dataObjectHelper->expects($this->once())
            ->method('mergeDataObjects')
            ->with(\Magento\Company\Api\Data\RoleInterface::class, $originalRole, $originalRole)
            ->willReturnSelf();
        $originalRole->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(1);
        $originalRole->expects($this->once())->method('getRoleName')->willReturn('Role Name');
        $this->companyRepository->expects($this->once())->method('get')->with(1)->willReturn($company);

        $this->model->retrieveRole($requestedRole);
    }

    /**
     * Test retrieveRole method without role name.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "role_name" is required. Enter and try again.
     */
    public function testRetrieveRoleWithoutRoleName()
    {
        $requestedRole = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requestedRole->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $requestedRole->expects($this->once())->method('getRoleName')->willReturn(null);

        $this->model->retrieveRole($requestedRole);
    }

    /**
     * Test retrieveRole method without role id.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage "company_id" is required. Enter and try again.
     */
    public function testRetrieveRoleWithInvalidRoleId()
    {
        $requestedRole = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $requestedRole->expects($this->once())->method('getRoleName')->willReturn('Role Name');
        $requestedRole->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $requestedRole->expects($this->once())->method('getCompanyId')->willReturn(null);

        $this->model->retrieveRole($requestedRole);
    }

    /**
     * Test retrieveRole method with NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with company_id = 1
     */
    public function testRetrieveRoleWithNoSuchEntityException()
    {
        $requestedRole = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('No such entity.'));
        $requestedRole->expects($this->once())->method('getRoleName')->willReturn('Role Name');
        $requestedRole->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $requestedRole->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(1);
        $this->companyRepository->expects($this->once())->method('get')->willThrowException($exception);

        $this->model->retrieveRole($requestedRole);
    }

    /**
     * Test validatePermissions method.
     *
     * @return void
     */
    public function testValidatePermissions()
    {
        $permission = $this->getMockBuilder(\Magento\Company\Api\Data\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $allowedResources = ['Magento_Company::users_view'];
        $permission->expects($this->once())->method('getResourceId')->willReturn('Magento_Company::users_view');

        $this->model->validatePermissions([$permission], $allowedResources);
    }

    /**
     * Test validatePermissions method with InputException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "Magento_Company::contacts" provided for the resource_id field.
     */
    public function testValidatePermissionsWithInputException()
    {
        $permission = $this->getMockBuilder(\Magento\Company\Api\Data\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $allowedResources = ['Magento_Company::contacts'];
        $permission->expects($this->once())->method('getResourceId')->willReturn('Magento_Company::users_view');

        $this->model->validatePermissions([$permission], $allowedResources);
    }

    /**
     * Test validateRoleBeforeDelete method.
     *
     * @return void
     */
    public function testValidateRoleBeforeDelete()
    {
        $roleId = 1;
        $companyId = 3;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $this->userRoleManagement->expects($this->once())
            ->method('getUsersCountByRoleId')
            ->with($roleId)
            ->willReturn(null);
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')
            ->with($companyId, false)
            ->willReturn([$role, $role]);

        $this->model->validateRoleBeforeDelete($role);
    }

    /**
     * Test validateRoleBeforeDelete method with users assigned to tole.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage This role cannot be deleted because users are assigned to it.
     */
    public function testValidateRoleBeforeDeleteWithUsersAssigned()
    {
        $roleId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $this->userRoleManagement->expects($this->once())
            ->method('getUsersCountByRoleId')
            ->with($roleId)
            ->willReturn(1);

        $this->model->validateRoleBeforeDelete($role);
    }

    /**
     * Test validateRoleBeforeDelete method when this role is the only one in the company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage You cannot delete a role when it is the only role in the company.
     */
    public function testValidateRoleBeforeDeleteWhenTheOnlyRole()
    {
        $roleId = 1;
        $companyId = 3;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $this->userRoleManagement->expects($this->once())
            ->method('getUsersCountByRoleId')
            ->with($roleId)
            ->willReturn(null);
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')
            ->with($companyId, false)
            ->willReturn([$role]);

        $this->model->validateRoleBeforeDelete($role);
    }

    /**
     * Test checkRoleExist method.
     *
     * @return void
     */
    public function testCheckRoleExist()
    {
        $roleId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getId')->willReturn($roleId);

        $this->model->checkRoleExist($role, $roleId);
    }

    /**
     * Test checkRoleExist method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with roleId = 1
     */
    public function testCheckRoleExistWithException()
    {
        $roleId = 1;
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getId')->willReturn(null);

        $this->model->checkRoleExist($role, $roleId);
    }
}
