<?php

namespace Magento\Company\Test\Unit\Block\Company\Management;

/**
 * Class AddTest.
 */
class AddTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roleManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Block\Company\Management\Add
     */
    private $add;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->roleManagement = $this->createMock(\Magento\Company\Api\RoleManagementInterface::class);
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->add = $objectManager->getObject(
            \Magento\Company\Block\Company\Management\Add::class,
            [
                'userContext' => $this->userContext,
                'roleManagement' => $this->roleManagement,
                'customerRepository' => $this->customerRepository,
                'data' => []
            ]
        );
    }

    /**
     * Test for getRoles method.
     *
     * @return void
     */
    public function testGetRoles()
    {
        $customerId = 1;
        $companyId = 2;
        $expectedResult = ['roles'];
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->with($customerId)->willReturn($customer);
        $customerExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCompanyAttributes']
        );
        $companyAttributes = $this->createMock(\Magento\Company\Api\Data\CompanyCustomerInterface::class);
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->once())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->roleManagement->expects($this->once())
            ->method('getRolesByCompanyId')->with($companyId)->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->add->getRoles());
    }
}
