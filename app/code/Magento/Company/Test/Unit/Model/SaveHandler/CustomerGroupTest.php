<?php

namespace Magento\Company\Test\Unit\Model\SaveHandler;

/**
 * Unit test for Magento\Company\Model\SaveHandler\CustomerGroup class.
 */
class CustomerGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Company\Model\SaveHandler\CustomerGroup
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\SaveHandler\CustomerGroup::class,
            [
                'customerRepository' => $this->customerRepository,
                'customerResource' => $this->customerResource,
            ]
        );
    }

    /**
     * Test execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $companyId = 1;
        $customerId = 100;
        $companyGroupId = 10;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $initialCompany = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $initialCompany->expects($this->once())->method('getId')->willReturn(1);
        $company->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($companyGroupId);
        $initialCompany->expects($this->once())->method('getCustomerGroupId')->willReturn(11);
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->customerResource->expects($this->once())
            ->method('getCustomerIdsByCompanyId')->with($companyId)->willReturn([$customerId]);
        $this->customerRepository->expects($this->once())
            ->method('getById')->with($customerId)->willReturn($customer);
        $customer->expects($this->once())->method('setGroupId')->with($companyGroupId)->willReturnSelf();
        $this->customerRepository->expects($this->once())->method('save')->with($customer)->willReturn($customer);
        $this->model->execute($company, $initialCompany);
    }
}
