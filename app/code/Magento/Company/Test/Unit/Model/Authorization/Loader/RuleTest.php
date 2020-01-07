<?php

namespace Magento\Company\Test\Unit\Model\Authorization\Loader;

/**
 * Unit test for \Magento\Company\Model\Authorization\Loader\Rule model.
 */
class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Acl\RootResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootResource;

    /**
     * @var \Magento\Company\Model\ResourceModel\Permission\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Framework\Acl\AclResource\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceProvider;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Model\Authorization\Loader\Rule
     */
    private $rule;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Permission\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->rootResource = $this->getMockBuilder(\Magento\Framework\Acl\RootResource::class)
            ->disableOriginalConstructor()->getMock();
        $this->resourceProvider = $this->getMockBuilder(\Magento\Framework\Acl\AclResource\ProviderInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->roleManagement = $this->getMockBuilder(\Magento\Company\Api\RoleManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyUser = $this->getMockBuilder(\Magento\Company\Model\CompanyUser::class)
            ->disableOriginalConstructor()->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->rule = $objectManagerHelper->getObject(
            \Magento\Company\Model\Authorization\Loader\Rule::class,
            [
                'rootResource' => $this->rootResource,
                'collection' => $this->collection,
                'resourceProvider' => $this->resourceProvider,
                'roleManagement' => $this->roleManagement,
                'companyUser' => $this->companyUser,
            ]
        );
    }

    /**
     * Test for populateAcl method.
     *
     * @param string $expectedPermission
     * @param int $getIdCounter
     * @param int $allowCounter
     * @param int $denyCounter
     * @param array $aclResources
     * @return void
     * @dataProvider populateAclDataProvider
     */
    public function testPopulateAcl(
        $expectedPermission,
        $getIdCounter,
        $allowCounter,
        $denyCounter,
        array $aclResources
    ) {
        $resourceId = 1;
        $companyId = 2;
        $roleId = 3;
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->roleManagement->expects($this->once())->method('getRolesByCompanyId')->with()->willReturn([$role]);
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with(\Magento\Company\Api\Data\PermissionInterface::ROLE_ID, ['in' => [$roleId]])->willReturnSelf();
        $permission = $this->getMockBuilder(\Magento\Company\Model\Permission::class)
            ->disableOriginalConstructor()->getMock();
        $this->collection->expects($this->once())->method('getItems')->willReturn([$permission]);
        $permission->expects($this->atLeastOnce())->method('getRoleId')->willReturn($roleId);
        $permission->expects($this->atLeastOnce())->method('getResourceId')->willReturn($resourceId);
        $permission->expects($this->atLeastOnce())->method('getPermission')->willReturn($expectedPermission);
        $acl = $this->getMockBuilder(\Magento\Framework\Acl::class)
            ->disableOriginalConstructor()->getMock();
        $acl->expects($this->once())->method('has')->with($resourceId)->willReturn(true);
        $this->rootResource->expects($this->exactly($getIdCounter))->method('getId')->willReturn(1);
        $acl->expects($this->exactly($allowCounter))->method('allow')->willReturnSelf();
        $acl->expects($this->exactly($denyCounter))->method('deny')->willReturnSelf();
        $this->resourceProvider->expects($this->once())->method('getAclResources')->willReturn($aclResources);

        $this->rule->populateAcl($acl);
    }

    /**
     * Data provider for populateAcl method.
     *
     * @return array
     */
    public function populateAclDataProvider()
    {
        return [
            [
                'allow',
                1,
                3,
                0,
                [
                    [
                        'children' => [
                            'children' => ['id' => 1]
                        ],
                        'id' => 2
                    ]
                ]
            ],
            [
                'deny',
                0,
                0,
                1,
                [
                    [
                        'children' => [
                            'children' => ['id' => 2]
                        ],
                        'id' => 1
                    ]
                ]
            ]
        ];
    }
}
