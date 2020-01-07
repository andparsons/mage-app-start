<?php

namespace Magento\Company\Test\Unit\Model\Role;

/**
 * Class for test RoleRepository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PermissionTest extends \PHPUnit\Framework\TestCase
{
    const ROLE_PERMISSION_ROLE_ID = 1;

    /**
     * @var \Magento\Company\Model\ResourceModel\Permission\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionCollectionFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Permission\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionCollection;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCache;

    /**
     * @var \Magento\Company\Api\AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRoleManagement;

    /**
     * @var \Magento\Company\Model\Role|\PHPUnit_Framework_MockObject_MockObject
     */
    private $role;

    /**
     * @var \Magento\Company\Model\Permission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Company\Model\Role\Permission
     */
    private $object;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->permissionCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Permission\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->permissionCollection = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Permission\Collection::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['addFieldToFilter', 'load', 'getItems'])
            ->getMock();
        $this->aclDataCache = $this->getMockBuilder(\Magento\Framework\Acl\Data\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRoleManagement = $this->getMockBuilder(\Magento\Company\Api\AclInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->permission = $this->getMockBuilder(\Magento\Company\Model\Permission::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->permission->setData($this->getPermissionArray());

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Company\Model\Role\Permission::class,
            [
                'permissionCollectionFactory' => $this->permissionCollectionFactory,
                'aclDataCache' => $this->aclDataCache,
                'userRoleManagement' => $this->userRoleManagement
            ]
        );
    }

    /**
     * Test getRoleUsersCount method.
     *
     * @return void
     */
    public function testGetRoleUsersCount()
    {
        $roleId = 7;
        $roles = [
            [1],
            [2],
            [3],
        ];
        $this->userRoleManagement->expects($this->once())->method('getUsersByRoleId')->with($roleId)
            ->willReturn($roles);

        $this->assertEquals(3, $this->object->getRoleUsersCount($roleId));
    }

    /**
     * Test getRolePermissions method.
     *
     * @return void
     */
    public function testGetRolePermissions()
    {
        $this->prepareGetRolePermissions();
        $this->assertEquals([$this->permission], $this->object->getRolePermissions($this->role));
    }

    /**
     * Test deleteRolePermissions method.
     *
     * @return void
     */
    public function testDeleteRolePermissions()
    {
        $this->prepareGetRolePermissions();
        $this->permission->expects($this->once())->method('delete');

        $this->aclDataCache->expects($this->exactly(1))->method('clean');

        $this->object->deleteRolePermissions($this->role);
    }

    /**
     * Test saveRolePermissions method.
     *
     * @return void
     */
    public function testSaveRolePermissions()
    {
        $this->prepareGetRolePermissions();
        $this->role->expects($this->once())
            ->method('getPermissions')
            ->willReturn([$this->permission]);

        $this->permission->expects($this->once())->method('delete');
        $this->permission->expects($this->once())->method('setRoleId')->with(self::ROLE_PERMISSION_ROLE_ID);
        $this->permission->expects($this->once())->method('save');
        $this->aclDataCache->expects($this->exactly(2))->method('clean');

        $this->object->saveRolePermissions($this->role);
    }

    /**
     * Prepare getRolePermissions data.
     *
     * @return void
     */
    private function prepareGetRolePermissions()
    {
        $this->role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getPermissions'])
            ->getMockForAbstractClass();
        $this->role->expects($this->atLeastOnce())->method('getId')->willReturn(self::ROLE_PERMISSION_ROLE_ID);
        $this->permissionCollectionFactory->expects($this->once())
            ->method('create')->willReturn($this->permissionCollection);
        $this->permissionCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('role_id', ['eq' => self::ROLE_PERMISSION_ROLE_ID])
            ->willReturnSelf();
        $this->permissionCollection->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->permissionCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->permission]);
    }

    /**
     * Get permissions array.
     *
     * @return array
     */
    private function getPermissionArray()
    {
        return [
            'permission_id' => 3,
            'role_id' => self::ROLE_PERMISSION_ROLE_ID,
            'resource_id' => 7,
            'permission' => 1
        ];
    }
}
