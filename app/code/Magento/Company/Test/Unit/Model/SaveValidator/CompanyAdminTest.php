<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for company admin validator.
 */
class CompanyAdminTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Framework\Exception\InputException|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exception;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\SaveValidator\CompanyAdmin
     */
    private $companyAdmin;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->exception = $this->getMockBuilder(\Magento\Framework\Exception\InputException::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyAdmin = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CompanyAdmin::class,
            [
                'company' => $this->company,
                'exception' => $this->exception,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $superUserId = 1;
        $companyId = 2;
            $this->company->expects($this->atLeastOnce())->method('getSuperUserId')->willReturn($superUserId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepository->expects($this->once())->method('getById')->with($superUserId)->willReturn($customer);
        $customerExtensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $companyAttributes = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getStatus')
            ->willReturn(\Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_ACTIVE);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->exception->expects($this->never())->method('addError');
        $this->companyAdmin->execute();
    }

    /**
     * Test for execute method with errors.
     *
     * @return void
     */
    public function testExecuteWithErrors()
    {
        $superUserId = 1;
        $companyId = 2;
        $this->company->expects($this->atLeastOnce())->method('getSuperUserId')->willReturn($superUserId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerRepository->expects($this->once())->method('getById')->with($superUserId)->willReturn($customer);
        $customerExtensionAttributes = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->setMethods(['getCompanyAttributes'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $companyAttributes = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getStatus')
            ->willReturn(\Magento\Company\Api\Data\CompanyCustomerInterface::STATUS_INACTIVE);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn(3);
        $this->company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->exception->expects($this->exactly(2))->method('addError')->withConsecutive(
            [__('The selected user is inactive. To continue, select another user or activate the current user.')],
            [__('This customer is a user of a different company. Enter a different email address to continue.')]
        )->willReturnSelf();
        $this->companyAdmin->execute();
    }
}
