<?php

namespace Magento\Company\Test\Unit\Model\Authorization\Loader;

/**
 * Test for \Magento\Company\Model\Authorization\Loader\Role model.
 */
class RoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Role\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var \Magento\Authorization\Model\Acl\Role\UserFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\CompanyUser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Model\Authorization\Loader\Role
     */
    private $role;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Role\Collection::class)
            ->disableOriginalConstructor()->getMock();
        $this->roleFactory = $this->getMockBuilder(\Magento\Authorization\Model\Acl\Role\UserFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->companyUser = $this->getMockBuilder(\Magento\Company\Model\CompanyUser::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->role = $objectManager->getObject(
            \Magento\Company\Model\Authorization\Loader\Role::class,
            [
                'collection' => $this->collection,
                'roleFactory' => $this->roleFactory,
                'companyUser' => $this->companyUser,
            ]
        );
    }

    /**
     * Test for populateAcl method.
     *
     * @return void
     */
    public function testPopulateAcl()
    {
        $companyId = 1;
        $roleId = 2;
        $this->companyUser->expects($this->once())->method('getCurrentCompanyId')->willReturn($companyId);
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with(\Magento\Company\Api\Data\RoleInterface::COMPANY_ID, $companyId)->willReturnSelf();
        $role = $this->getMockBuilder(\Magento\Company\Api\Data\RoleInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->collection->expects($this->once())->method('getItems')->willReturn([$role]);
        $acl = $this->getMockBuilder(\Magento\Framework\Acl::class)
            ->disableOriginalConstructor()->getMock();
        $aclRole = $this->getMockBuilder(\Magento\Authorization\Model\Acl\Role\User::class)
            ->disableOriginalConstructor()->getMock();
        $role->expects($this->once())->method('getId')->willReturn($roleId);
        $this->roleFactory->expects($this->once())->method('create')->with(['roleId' => $roleId])->willReturn($aclRole);
        $acl->expects($this->once())->method('addRole')->with($aclRole)->willReturnSelf();
        $this->role->populateAcl($acl);
    }
}
