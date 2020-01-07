<?php

namespace Magento\Company\Test\Unit\Block\Company\Role;

/**
 * Class EditTest.
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Api\Data\RoleInterfaceFactory|\PHPUnit\Framework\MockObject_MockObject
     */
    private $roleFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit\Framework\MockObject_MockObject
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $resourceProvider;

    /**
     * @var \Magento\Company\Model\Authorization\PermissionProvider|\PHPUnit\Framework\MockObject_MockObject
     */
    private $permissionProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Company\Block\Company\Role\Edit
     */
    private $edit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->roleRepository = $this->createMock(\Magento\Company\Api\RoleRepositoryInterface::class);
        $this->roleFactory = $this->createPartialMock(
            \Magento\Company\Api\Data\RoleInterfaceFactory::class,
            ['create']
        );
        $this->jsonHelper = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $this->resourceProvider = $this->createMock(\Magento\Framework\Acl\AclResource\ProviderInterface::class);
        $this->permissionProvider = $this->createMock(\Magento\Company\Model\Authorization\PermissionProvider::class);
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->edit = $objectManager->getObject(
            \Magento\Company\Block\Company\Role\Edit::class,
            [
                'roleRepository' => $this->roleRepository,
                'roleFactory' => $this->roleFactory,
                'jsonHelper' => $this->jsonHelper,
                'resourceProvider' => $this->resourceProvider,
                'permissionProvider' => $this->permissionProvider,
                '_request' => $this->request,
                'data' => []
            ]
        );
    }

    /**
     * Test for getRole method.
     *
     * @return void
     */
    public function testGetRole()
    {
        $roleId = 1;
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $this->request->expects($this->exactly(2))->method('getParam')->with('id')->willReturn($roleId);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $this->assertEquals($role, $this->edit->getRole());
    }

    /**
     * Test for getRole method with duplicate.
     *
     * @return void
     */
    public function testGetRoleWithDuplicate()
    {
        $roleId = 1;
        $roleName = 'Role 1';
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn(null);
        $this->request->expects($this->at(1))->method('getParam')->with('duplicate_id')->willReturn($roleId);
        $this->request->expects($this->at(2))->method('getParam')->with('id')->willReturn(null);
        $this->roleRepository->expects($this->once())->method('get')->with($roleId)->willReturn($role);
        $role->expects($this->once())->method('setId')->with(null)->willReturnSelf();
        $role->expects($this->once())->method('getRoleName')->willReturn($roleName);
        $role->expects($this->once())->method('setRoleName')->with($roleName . __(' - Duplicated'))->willReturnSelf();
        $this->assertEquals($role, $this->edit->getRole());
    }

    /**
     * Test for getRole with empty id.
     *
     * @return void
     */
    public function testGetRoleWithEmptyId()
    {
        $role = $this->createMock(\Magento\Company\Api\Data\RoleInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn(null);
        $this->request->expects($this->at(1))->method('getParam')->with('duplicate_id')->willReturn(null);
        $this->roleFactory->expects($this->once())->method('create')->willReturn($role);
        $this->assertEquals($role, $this->edit->getRole());
    }

    /**
     * Test for getTreeJsOptions method.
     *
     * @return void
     */
    public function testGetTreeJsOptions()
    {
        $roleId = 1;
        $rolePermissions = [
            1 => 'allow'
        ];
        $aclResources = [
            [
                'id' => 1,
                'title' => 'Resource 1',
                'sort_order' => 10,
                'children' => [
                    [
                        'id' => 3,
                        'title' => 'Subresource 1',
                        'sort_order' => 15,
                    ],
                ],
            ],
            [
                'id' => 2,
                'title' => 'Resource 2',
                'sort_order' => 20,
                'children' => [],
            ],

        ];
        $this->request->expects($this->once())->method('getParam')->with('id')->willReturn($roleId);
        $this->permissionProvider->expects($this->once())
            ->method('retrieveRolePermissions')->with($roleId)->willReturn($rolePermissions);
        $this->resourceProvider->expects($this->once())->method('getAclResources')->willReturn($aclResources);
        $this->assertEquals(
            [
                'roleTree' => [
                    'data' => [
                        [
                            'id' => 1,
                            'children' => [
                                [
                                    'id' => 3,
                                    'text' => 'Subresource 1',
                                    'state' => [],
                                ],
                            ],
                            'text' => 'Resource 1',
                            'state' => [
                                'opened' => 'open',
                                'selected' => true,
                            ],
                            'li_attr' => [
                                'class' => 'root-collapsible',
                            ],
                        ],
                        [
                            'id' => 2,
                            'children' => [],
                            'text' => 'Resource 2',
                            'state' => [],
                            'li_attr' => [
                                'class' => 'root-collapsible',
                            ],
                        ],
                    ],
                ],
            ],
            $this->edit->getTreeJsOptions()
        );
    }
}
