<?php

namespace Magento\Company\Test\Unit\Model\Authorization;

/**
 * Class RoleLocatorTest.
 */
class RoleLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @param \Magento\Company\Model\CompanyAdminPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adminPermission;

    /**
     * @var \Magento\Company\Model\Authorization\RoleLocator
     */
    private $roleLocatorModel;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockForAbstractClass(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getUserId']
        );
        $this->roleManagement = $this->getMockForAbstractClass(
            \Magento\Company\Api\AclInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getRolesByUserId']
        );
        $this->adminPermission = $this->createMock(
            \Magento\Company\Model\CompanyAdminPermission::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->roleLocatorModel = $objectManagerHelper->getObject(
            \Magento\Company\Model\Authorization\RoleLocator::class,
            [
                'userContext' => $this->userContext,
                'roleManagement' => $this->roleManagement,
                'adminPermission' => $this->adminPermission,
            ]
        );
    }

    /**
     * Test getAclRoleId method.
     *
     * @param array|null $role
     * @param int $roleId
     * @return void
     * @dataProvider getAclRoleIdDataProvider
     */
    public function testGetAclRoleId($role, $roleId)
    {
        $userId = 1;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->roleManagement->expects($this->once())->method('getRolesByUserId')->with($userId)->willReturn($role);
        if (!empty($role)) {
            $role = array_shift($role);
            $role->expects($this->once())->method('getData')->with('role_id')->willReturn(1);
        } else {
            $this->adminPermission->expects($this->once())->method('isGivenUserCompanyAdmin')->with($userId)
                ->willReturn(true);
        }
        $this->assertEquals($roleId, $this->roleLocatorModel->getAclRoleId());
    }

    /**
     * Data provider for getAclRoleId method.
     *
     * @return array
     */
    public function getAclRoleIdDataProvider()
    {
        $role = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\RoleInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getData']
        );
        return [
            [[$role], 1],
            [null, 0]
        ];
    }
}
