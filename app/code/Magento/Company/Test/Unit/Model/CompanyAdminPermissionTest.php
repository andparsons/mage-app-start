<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Class for test CompanyAdminPermission.
 */
class CompanyAdminPermissionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\CompanyAdminPermission
     */
    private $companyAdminPermission;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->userContext = $this->getMockForAbstractClass(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getUserId']
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyAdminPermission = $objectManager->getObject(
            \Magento\Company\Model\CompanyAdminPermission::class,
            [
                'customerContext' => $this->userContext,
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository
            ]
        );
    }

    /**
     * Test isCurrentUserCompanyAdmin method.
     *
     * @param int $isCurrentUserCompanyAdmin
     * @param int $customerId
     * @return void
     * @dataProvider isCurrentUserCompanyAdminDataProvider
     */
    public function testIsCurrentUserCompanyAdmin($isCurrentUserCompanyAdmin, $customerId)
    {
        $userId = 1;
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->prepareIsUserCompanyAdminMock($customer);
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->setMethods(['getSuperUserId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository->expects($this->once())->method('get')->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($userId);
        $this->assertEquals($isCurrentUserCompanyAdmin, $this->companyAdminPermission->isCurrentUserCompanyAdmin());
    }

    /**
     * Test for isCurrentUserCompanyAdmin method when company repository returns NoSuchEntityException.
     *
     * @return void
     */
    public function testIsCurrentUserCompanyAdminWithNoSuchEntityException()
    {
        $userId = 1;
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->prepareIsUserCompanyAdminMock($customer);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__('Exception message'));
        $this->companyRepository->expects($this->once())->method('get')->willThrowException($exception);
        $this->assertEquals(false, $this->companyAdminPermission->isCurrentUserCompanyAdmin());
    }

    /**
     * Data provider for isCurrentUserCompanyAdmin method.
     *
     * @return array
     */
    public function isCurrentUserCompanyAdminDataProvider()
    {
        return [
            [1, 1],
            [0, 2]
        ];
    }

    /**
     * Test isGivenUserCompanyAdmin method.
     *
     * @param int $isCurrentUserCompanyAdmin
     * @param int $customerId
     * @return void
     * @dataProvider isGivenUserCompanyAdminDataProvider
     */
    public function testIsGivenUserCompanyAdmin($isCurrentUserCompanyAdmin, $customerId)
    {
        $userId = 1;
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
        $this->prepareIsUserCompanyAdminMock($customer);
        $company = $this->getMockBuilder(\Magento\Company\Model\Company::class)
            ->setMethods(['getSuperUserId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository->expects($this->once())->method('get')->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($userId);

        $this->assertEquals(
            $isCurrentUserCompanyAdmin,
            $this->companyAdminPermission->isGivenUserCompanyAdmin($userId)
        );
    }

    /**
     * Data provider for isCurrentUserCompanyAdmin method.
     *
     * @return array
     */
    public function isGivenUserCompanyAdminDataProvider()
    {
        return [
            [1, 1],
            [0, 2]
        ];
    }

    /**
     * Mock for isUserCompanyAdmin method.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $customer
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareIsUserCompanyAdminMock(\PHPUnit_Framework_MockObject_MockObject $customer)
    {
        $customerExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCompanyAttributes']
        );
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $customer->expects($this->exactly(3))
            ->method('getExtensionAttributes')
            ->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->exactly(2))->method('getCompanyAttributes')
            ->willReturn($companyAttributes);

        return $companyAttributes;
    }
}
