<?php

namespace Magento\Company\Test\Unit\Block\Adminhtml\Customer\Edit\Tab\View;

use \Magento\Company\Block\Adminhtml\Customer\Edit\Tab\View\PersonalInfo;
use \Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Unit test for Magento\Company\Block\Adminhtml\Customer\Edit\Tab\View\PersonalInfo class.
 */
class PersonalInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepositoryMock;

    /**
     * @var CompanyCustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerAttributesMock;

    /**
     * @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendSessionMock;

    /**
     * @var PersonalInfo
     */
    private $personalInfo;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepositoryMock = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendSessionMock = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerData'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->personalInfo = $objectManager->getObject(
            \Magento\Company\Block\Adminhtml\Customer\Edit\Tab\View\PersonalInfo::class,
            [
                'customerRepository' => $this->customerRepositoryMock,
                'companyRepository' => $this->companyRepositoryMock,
                '_backendSession' => $this->backendSessionMock,
                'customerAttributes' => $this->customerAttributesMock,
            ]
        );
    }

    /**
     * Test getCustomerAttributes method.
     *
     * @param array $result
     * @return void
     * @dataProvider getCustomerAttributesDataProvider
     */
    public function testGetCustomerAttributes(array $result)
    {
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendSessionMock->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->willReturn(['account' => ['id' => 4]]);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customer);
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $this->customerAttributesMock = $result['customerAttributes'];
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->once())->method('getCompanyAttributes')
            ->willReturn($this->customerAttributesMock);

        $this->assertEquals($result['customerAttributes'], $this->personalInfo->getCustomerAttributes());
    }

    /**
     * Data provider for getCustomerAttributes method.
     *
     * @return array
     */
    public function getCustomerAttributesDataProvider()
    {
        $customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        return [
            [
                ['customerAttributes' => $customerAttributes]
            ],
            [
                ['customerAttributes' => null]
            ]
        ];
    }

    /**
     * Set customer attributes mock.
     *
     * @return void
     */
    private function getCustomerAttributes()
    {
        $jobTitle = 'Manager';
        $companyName = 'Company 1';
        $companyId = 17;
        $customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerAttributes->expects($this->any())->method('getJobTitle')->willReturn($jobTitle);
        $company->expects($this->any())->method('getCompanyName')->willReturn($companyName);
        $customerAttributes->expects($this->any())->method('getCompanyId')->willReturn($companyId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customer);
        $this->companyRepositoryMock->expects($this->any())->method('get')->willReturn($company);
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->once())->method('getCompanyAttributes')
            ->willReturn($customerAttributes);
    }

    /**
     * Test getJobTitle method.
     *
     * @return void
     */
    public function testGetJobTitle()
    {
        $jobTitle = 'Manager';
        $this->backendSessionMock->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->willReturn(['account' => ['id' => 4]]);
        $this->getCustomerAttributes();
        $this->assertEquals($jobTitle, $this->personalInfo->getJobTitle());
    }

    /**
     * Test getCompanyName method.
     *
     * @return void
     */
    public function testGetCompanyName()
    {
        $companyName = 'Company 1';
        $this->backendSessionMock->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->willReturn(['account' => ['id' => 4]]);
        $this->getCustomerAttributes();
        $this->assertEquals($companyName, $this->personalInfo->getCompanyName());
    }

    /**
     * Test getCompany method.
     *
     * @return void
     */
    public function testGetCompany()
    {
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendSessionMock->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->willReturn(['account' => ['id' => 4]]);
        $this->getCustomerAttributes();
        $this->assertEquals($company, $this->personalInfo->getCompany());
    }

    /**
     * Test getCustomerType method.
     *
     * @param int $companyId
     * @param int $customerId
     * @param int $superUserId
     * @param int $expectedResult
     * @return void
     * @dataProvider getCustomerTypeDataProvider
     */
    public function testGetCustomerType(
        $companyId,
        $customerId,
        $superUserId,
        $expectedResult
    ) {
        $customerAttributes = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendSessionMock->expects($this->atLeastOnce())
            ->method('getCustomerData')
            ->willReturn(
                [
                    'account' => ['id' => $customerId]
                ]
            );
        $customerAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($superUserId);
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customer);
        $this->companyRepositoryMock->expects($this->once())->method('get')->willReturn($company);
        $customerExtensionAttributes = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->once())->method('getCompanyAttributes')
            ->willReturn($customerAttributes);

        $this->assertEquals($expectedResult, $this->personalInfo->getCustomerType());
    }

    /**
     * Data provider for testGetCustomerType method.
     *
     * @return array
     */
    public function getCustomerTypeDataProvider()
    {
        return [
            [1, 1, 1, CompanyCustomerInterface::TYPE_COMPANY_ADMIN],
            [1, 1, 2, CompanyCustomerInterface::TYPE_COMPANY_USER]
        ];
    }
}
