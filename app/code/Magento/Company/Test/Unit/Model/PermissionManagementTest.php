<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\PermissionManagement class.
 */
class PermissionManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourcePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourcePool;

    /**
     * @var \Magento\Company\Api\Data\PermissionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionFactory;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceProvider;

    /**
     * @var \Magento\Company\Model\PermissionManagement
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resourceProvider = $this->getMockBuilder(\Magento\Framework\Acl\AclResource\ProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->permissionFactory = $this->getMockBuilder(\Magento\Company\Api\Data\PermissionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->resourcePool = $this->getMockBuilder(\Magento\Company\Model\ResourcePool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\PermissionManagement::class,
            [
                'resourceProvider' => $this->resourceProvider,
                'permissionFactory' => $this->permissionFactory,
                'resourcePool' => $this->resourcePool,
            ]
        );
    }

    /**
     * Test retrieveDefaultPermissions method.
     *
     * @param array $aclResources
     * @param array $allowedResources
     * @return void
     * @dataProvider retrieveDefaultPermissionsDataProvider
     */
    public function testRetrieveDefaultPermissions(array $aclResources, array $allowedResources)
    {
        $permission = $this->getMockBuilder(\Magento\Company\Api\Data\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourcePool->expects($this->once())->method('getDefaultResources')->willReturn($allowedResources);
        $this->resourceProvider->expects($this->once())->method('getAclResources')->willReturn($aclResources);
        $this->permissionFactory->expects($this->exactly(3))->method('create')->willReturn($permission);
        $permission->expects($this->exactly(3))
            ->method('setPermission')
            ->withConsecutive(['allow'], ['allow'], ['deny'])
            ->willReturnSelf();
        $permission->expects($this->exactly(3))
            ->method('setResourceId')
            ->withConsecutive([1], [3], [2])
            ->willReturnSelf();
        $this->assertEquals([$permission, $permission, $permission], $this->model->retrieveDefaultPermissions());
    }

    /**
     * Data provider for retrieveDefaultPermissions method.
     *
     * @return array
     */
    public function retrieveDefaultPermissionsDataProvider()
    {
        return [
            [
                [
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

                ],
                [1, 3]
            ]
        ];
    }

    /**
     * Test retrieveAllowedResources method.
     *
     * @return void
     */
    public function testRetrieveAllowedResources()
    {
        $permission = $this->getMockBuilder(\Magento\Company\Api\Data\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $permission->expects($this->once())
            ->method('getPermission')
            ->willReturn(\Magento\Company\Api\Data\PermissionInterface::ALLOW_PERMISSION);
        $permission->expects($this->once())
            ->method('getResourceId')
            ->willReturn('Magento_Company::contacts');

        $this->assertEquals(['Magento_Company::contacts'], $this->model->retrieveAllowedResources([$permission]));
    }
}
