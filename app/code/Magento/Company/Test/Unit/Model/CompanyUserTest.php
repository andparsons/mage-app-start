<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Class CompanyUserTest.
 */
class CompanyUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyUser = $objectManager->getObject(
            \Magento\Company\Model\CompanyUser::class,
            [
                'userContext' => $this->userContext,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }

    /**
     * Test getCurrentCompanyId method.
     *
     * @return void
     */
    public function testGetCurrentCompanyId()
    {
        $userId = 1;
        $companyId = 1;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())
            ->method('getCompanyId')
            ->willReturn($companyId);

        $this->assertEquals($companyId, $this->companyUser->getCurrentCompanyId());
    }

    /**
     * Test getCurrentCompanyId method for customer without company attributes.
     *
     * @return void
     */
    public function testGetCurrentCompanyIdWithoutCompanyAttributes()
    {
        $userId = 1;
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')
            ->willReturn(null);

        $this->assertEquals(null, $this->companyUser->getCurrentCompanyId());
    }
}
