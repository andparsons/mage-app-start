<?php

namespace Magento\Company\Test\Unit\Model\Action\Customer;

use Magento\Company\Api\AclInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\RoleInterface;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Company\Model\Action\Customer\Assign;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for Magento\Company\Model\Action\Customer\Assign class.
 */
class AssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AclInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $acl;

    /**
     * @var RoleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleRepository;

    /**
     * @var Assign
     */
    private $assign;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->acl = $this->getMockBuilder(AclInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->roleRepository = $this->getMockBuilder(RoleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->assign = $objectManager->getObject(
            Assign::class,
            [
                'acl' => $this->acl,
                'roleRepository' => $this->roleRepository,
            ]
        );
    }

    /**
     * Unit test for 'assignCustomerRole' method.
     *
     * @param int $roleId
     * @param int $companyId
     * @return void
     *
     * @dataProvider assignCustomerRoleDataProvider
     */
    public function testAssignCustomerRole($roleId, $companyId)
    {
        $role = $this->getMockBuilder(RoleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $role->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleRepository->expects($this->once())
            ->method('get')
            ->willReturn($role);
        $customer = $this->getPreparedCustomerMock();
        $this->assertInstanceOf(
            CustomerInterface::class,
            $this->assign->assignCustomerRole($customer, $roleId)
        );
    }

    /**
     * Data provider for 'testAssignCustomerRole' method.
     *
     * @return array
     */
    public function assignCustomerRoleDataProvider()
    {
        return [
            [1, 1],
            [1, 2],
            [null, 1],
            [1, null],
            [null, null],
        ];
    }

    /**
     * Getter for customer mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPreparedCustomerMock()
    {
        $companyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn(1);
        $extensionAttributes = $this
            ->getMockBuilder(CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        return $customer;
    }
}
