<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\RoleManagement class.
 */
class RoleManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Role\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleCollectionFactory;

    /**
     * @var \Magento\Company\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\RoleManagement
     */
    private $roleManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->roleFactory = $this->getMockBuilder(\Magento\Company\Model\RoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->roleCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Role\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->roleManagement = $objectManager->getObject(
            \Magento\Company\Model\RoleManagement::class,
            [
                'roleCollectionFactory' => $this->roleCollectionFactory,
                'roleFactory' => $this->roleFactory,
            ]
        );
    }

    /**
     * Test getRolesByCompanyId method.
     *
     * @return void
     */
    public function testGetRolesByCompanyId()
    {
        $companyId = 1;
        $roleCollection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Role\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $roleModel = $this->getMockBuilder(\Magento\Company\Model\Role::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleCollectionFactory->expects($this->once())->method('create')->willReturn($roleCollection);
        $roleCollection->expects($this->once())->method('addFieldToFilter')
            ->with('company_id', ['eq' => $companyId])
            ->willReturnSelf();
        $roleCollection->expects($this->once())->method('setOrder')->with('role_id', 'ASC')->willReturnSelf();
        $roleCollection->expects($this->once())->method('load')->willReturnSelf();
        $roleCollection->expects($this->once())->method('getItems')->willReturn([]);
        $this->roleFactory->expects($this->once())->method('create')->willReturn($roleModel);
        $roleModel->expects($this->once())->method('setId')->with(0)->willReturnSelf();
        $roleModel->expects($this->once())->method('setRoleName')->with('Company Administrator')->willReturnSelf();

        $this->assertEquals([$roleModel], $this->roleManagement->getRolesByCompanyId($companyId));
    }

    /**
     * Test getCompanyDefaultRole method.
     *
     * @return void
     */
    public function testGetCompanyDefaultRole()
    {
        $companyId = 1;
        $roleCollection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Role\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->roleCollectionFactory->expects($this->once())->method('create')->willReturn($roleCollection);
        $roleCollection->expects($this->once())->method('addFieldToFilter')
            ->with('company_id', ['eq' => $companyId])
            ->willReturnSelf();
        $roleCollection->expects($this->once())->method('setOrder')->with('role_id', 'ASC')->willReturnSelf();
        $roleCollection->expects($this->once())->method('load')->willReturnSelf();
        $roleCollection->expects($this->once())->method('getItems')->willReturn([$role]);

        $this->assertEquals($role, $this->roleManagement->getCompanyDefaultRole($companyId));
    }
}
