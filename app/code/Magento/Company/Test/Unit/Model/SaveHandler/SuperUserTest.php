<?php

namespace Magento\Company\Test\Unit\Model\SaveHandler;

use Magento\Company\Api\Data\CompanyCustomerInterfaceFactory;

/**
 * Test for SuperUser.
 */
class SuperUserTest extends \PHPUnit\Framework\TestCase
{
    const SUPERUSER_TEST_USER_ID = 1;
    const SUPERUSER_TEST_COMPANY_ID = 8;

    /**
     * @var \Magento\Company\Model\CompanySuperUserSave|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companySuperUser;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialCompany;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCustomerFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $admin;

    /**
     * @var \Magento\Customer\Api\Data\CustomerExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributes;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyAttributes;

    /**
     * @var \Magento\Company\Model\SaveHandler\SuperUser
     */
    private $object;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->customerRepository = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById', 'save'])
            ->getMockForAbstractClass();
        $this->companySuperUser = $this->getMockBuilder(\Magento\Company\Model\CompanySuperUserSave::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveCustomer'])
            ->getMock();
        $this->companyCustomerFactory = $this->getMockBuilder(CompanyCustomerInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->extensionAttributes = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes', 'setCompanyAttributes'])
            ->getMockForAbstractClass();
        $this->companyAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyId'])
            ->getMockForAbstractClass();
        $this->admin = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSuperUserId', 'getId', 'getStatus'])
            ->getMockForAbstractClass();
        $this->initialCompany = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSuperUserId'])
            ->getMockForAbstractClass();
        $this->company->expects($this->exactly(1))->method('getId')
            ->willReturn(self::SUPERUSER_TEST_COMPANY_ID);
        $this->company->expects($this->atLeastOnce())->method('getStatus')
            ->willReturn(\Magento\Company\Api\Data\CompanyInterface::STATUS_APPROVED);
        $this->company->expects($this->exactly(2))->method('getSuperUserId')
            ->willReturn(self::SUPERUSER_TEST_USER_ID);
        $this->initialCompany->expects($this->exactly(3))->method('getSuperUserId')
            ->willReturn(33);
        $this->customerRepository->expects($this->atLeastOnce())
            ->method('getById')->withConsecutive([self::SUPERUSER_TEST_USER_ID], [33])
            ->willReturnOnConsecutiveCalls($this->admin, null);
        $this->companySuperUser->expects($this->once())->method('saveCustomer')->with($this->admin);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManagerHelper->getObject(
            \Magento\Company\Model\SaveHandler\SuperUser::class,
            [
                'customerRepository' => $this->customerRepository,
                'companySuperUser' => $this->companySuperUser,
                'companyCustomerAttributes' => $this->companyCustomerFactory
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
        $this->admin->expects($this->exactly(2))->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturn($this->companyAttributes);
        $this->companyAttributes->expects($this->once())->method('setCompanyId');

        $this->object->execute($this->company, $this->initialCompany);
    }

    /**
     * Test for execute method when company attributes absent.
     *
     * @return void
     */
    public function testExecuteWithAbsentCompanyAttributes()
    {
        $this->admin->expects($this->exactly(3))->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->companyAttributes
            );
        $this->companyCustomerFactory->expects($this->once())->method('create')->willReturn($this->companyAttributes);
        $this->extensionAttributes->expects($this->once())->method('setCompanyAttributes')
            ->with($this->companyAttributes);
        $this->companyAttributes->expects($this->once())->method('setCompanyId');

        $this->object->execute($this->company, $this->initialCompany);
    }
}
