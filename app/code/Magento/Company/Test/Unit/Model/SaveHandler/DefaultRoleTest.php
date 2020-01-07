<?php
namespace Magento\Company\Test\Unit\Model\SaveHandler;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Company\Model\PermissionManagementInterface;

/**
 * Unit tests for DefaultRole save handler.
 */
class DefaultRoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Company\Model\SaveHandler\DefaultRole
     */
    private $defaultRole;

    /**
     * @var \Magento\Company\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactoryMock;

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepositoryMock;

    /**
     * @var \Magento\Company\Model\PermissionManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionManagementMock;

    /**
     * @var \Magento\Company\Model\RoleManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagementMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->roleFactoryMock = $this->getMockBuilder(\Magento\Company\Model\RoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->roleRepositoryMock = $this->getMockBuilder(\Magento\Company\Api\RoleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->permissionManagementMock = $this->getMockBuilder(PermissionManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleManagementMock = $this->getMockBuilder(\Magento\Company\Model\RoleManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->defaultRole = $this->objectManagerHelper->getObject(
            \Magento\Company\Model\SaveHandler\DefaultRole::class,
            [
                'roleFactory' => $this->roleFactoryMock,
                'roleRepository' => $this->roleRepositoryMock,
                'permissionManagement' => $this->permissionManagementMock,
                'roleManagement' => $this->roleManagementMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $roleName = 'role name';
        $companyId = 1;
        $permissions = [];

        $companyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompanyMock = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $initialCompanyMock->expects($this->once())->method('getId')->willReturn(null);
        $roleMock = $this->getMockBuilder(\Magento\Company\Model\Role::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleMock);
        $this->roleManagementMock->expects($this->once())->method('getCompanyDefaultRoleName')->willReturn($roleName);
        $roleMock->expects($this->once())->method('setRoleName')->with($roleName);
        $companyMock->expects($this->once())->method('getId')->willReturn($companyId);
        $roleMock->expects($this->once())->method('setCompanyId')->with($companyId);
        $this->permissionManagementMock->expects($this->once())->method('retrieveDefaultPermissions')
            ->willReturn($permissions);
        $roleMock->expects($this->once())->method('setPermissions')->with($permissions);
        $this->roleRepositoryMock->expects($this->once())->method('save')->with($roleMock);

        $this->defaultRole->execute($companyMock, $initialCompanyMock);
    }
}
