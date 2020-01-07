<?php

namespace Magento\Company\Test\Unit\Model\Authorization;

/**
 * Class PermissionProviderTest.
 */
class PermissionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Permission\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionCollection;

    /**
     * @var \Magento\Company\Model\ResourcePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourcePool;

    /**
     * @var \Magento\Company\Model\Authorization\PermissionProvider
     */
    private $permissionProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->permissionCollection = $this->createMock(
            \Magento\Company\Model\ResourceModel\Permission\Collection::class
        );
        $this->resourcePool = $this->createMock(
            \Magento\Company\Model\ResourcePool::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->permissionProvider = $objectManager->getObject(
            \Magento\Company\Model\Authorization\PermissionProvider::class,
            [
                'permissionCollection' => $this->permissionCollection,
                'resourcePool' => $this->resourcePool,
            ]
        );
    }

    /**
     * Test retrieve role permissions.
     *
     * @return void
     */
    public function testRetrieveRolePermissions()
    {
        $roleId = 1;
        $this->permissionCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('role_id', ['eq' => $roleId])
            ->willReturnSelf();
        $this->permissionCollection->expects($this->once())
            ->method('toOptionHash')
            ->with('resource_id', 'permission')
            ->willReturnSelf();

        $this->assertInstanceOf(
            \Magento\Company\Model\ResourceModel\Permission\Collection::class,
            $this->permissionProvider->retrieveRolePermissions($roleId)
        );
    }

    /**
     * Test retrieveDefaultPermissions method.
     *
     * @param array $allowedResources
     * @param array $expectedResult
     * @return void
     * @dataProvider retrieveDefaultPermissionsDataProvider
     */
    public function testRetrieveDefaultPermissions(array $allowedResources, array $expectedResult)
    {
        $this->resourcePool->expects($this->once())->method('getDefaultResources')->willReturn($allowedResources);
        $this->assertEquals($expectedResult, $this->permissionProvider->retrieveDefaultPermissions());
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
                ['Magento_NegotiableQuote::checkout', 'Magento_NegotiableQuote::view_quotes'],
                [
                    'Magento_NegotiableQuote::checkout' => 'allow',
                    'Magento_NegotiableQuote::view_quotes' => 'allow',
                ]
            ]
        ];
    }
}
