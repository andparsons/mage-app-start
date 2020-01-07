<?php

namespace Magento\Company\Test\Unit\Model\Customer;

/**
 * Unit test for Magento\Company\Model\Customer\Company class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyFactory;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStructure;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAttributes;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupManagement;

    /**
     * @var \Magento\Company\Model\Customer\Company
     */
    private $customerCompany;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyFactory = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyStructure = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerCompany = $objectManagerHelper->getObject(
            \Magento\Company\Model\Customer\Company::class,
            [
                'companyFactory' => $this->companyFactory,
                'companyRepository' => $this->companyRepository,
                'companyStructure' => $this->companyStructure,
                'customerAttributes' => $this->customerAttributes,
                'customerResource' => $this->customerResource,
                'groupManagement' => $this->groupManagement,
            ]
        );
    }

    /**
     * Test for createCompany method.
     *
     * @return void
     */
    public function testCreateCompany()
    {
        $customerId = 666;
        $companyId = 555;
        $jobTitle = 'job title';
        $company = ['name' => 'company 1'];

        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $companyDataObject = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyDataObject->expects($this->once())->method('setSuperUserId')->with($customerId);
        $companyDataObject->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyFactory->expects($this->once())
            ->method('create')->with(['data' => $company])->willReturn($companyDataObject);
        $this->companyRepository->expects($this->once())
            ->method('save')->with($companyDataObject)->willReturn($companyDataObject);
        $this->customerAttributes->expects($this->once())->method('setCompanyId')->with($companyId)->willReturnSelf();
        $this->customerAttributes->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $this->customerAttributes->expects($this->once())->method('setJobTitle')->with($jobTitle)->willReturnSelf();
        $this->customerResource->expects($this->once())
            ->method('saveAdvancedCustomAttributes')->with($this->customerAttributes);
        $group = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $group->expects($this->once())->method('getId')->willReturn(1);
        $this->groupManagement->expects($this->once())->method('getDefaultGroup')->willReturn($group);

        $this->assertSame($companyDataObject, $this->customerCompany->createCompany($customer, $company, $jobTitle));
    }
}
