<?php

namespace Magento\Company\Test\Unit\CustomerData;

/**
 * Unit tests for \Magento\Company\CustomerData\Company model.
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\CompanyContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyContext;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permission;

    /**
     * @var \Magento\Company\CustomerData\Company
     */
    private $customerData;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepository = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->companyContext = $this->createMock(\Magento\Company\Model\CompanyContext::class);
        $this->permission  = $this
            ->getMockBuilder(\Magento\Company\Model\Customer\PermissionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isCheckoutAllowed', 'isLoginAllowed', 'isCompanyLocked'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->customerData = $objectManager->getObject(
            \Magento\Company\CustomerData\Company::class,
            [
                'customerRepository' => $this->customerRepository,
                'companyContext' => $this->companyContext,
                'permission' => $this->permission
            ]
        );
    }

    /**
     * Test getSectionData.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @param bool $isCheckoutAllowed
     * @param bool $isLoginAllowed
     * @param array $expectedResult
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $counter
     * @return void
     * @dataProvider dataProviderGetSectionData
     */
    public function testGetSectionData(
        $customer,
        $isCheckoutAllowed,
        $isLoginAllowed,
        array $expectedResult,
        \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder $counter
    ) {
        $customerId = 1;

        $this->companyContext->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->with($customerId)->willReturn($customer);
        if ($customer) {
            $this->permission->expects($this->once())
                ->method('isCheckoutAllowed')
                ->with($customer)
                ->willReturn($isCheckoutAllowed);
            $this->companyContext->expects($this->once())->method('isStorefrontRegistrationAllowed')->willReturn(false);
            $this->permission->expects($this->once())
                ->method('isLoginAllowed')
                ->with($customer)
                ->willReturn($isLoginAllowed);
        }
        $this->permission->expects($counter)->method('isCompanyBlocked')->willReturn(false);

        $this->assertEquals($expectedResult, $this->customerData->getSectionData());
    }

    /**
     * Data provider getSectionData.
     *
     * @return array
     */
    public function dataProviderGetSectionData()
    {
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        return [
            [
                $customer,
                true,
                true,
                [
                    'is_checkout_allowed' => true,
                    'is_login_allowed' => true,
                    'is_enabled' => false,
                    'is_company_blocked' => false,
                    'has_customer_company' => false,
                    'is_storefront_registration_allowed' => false
                ],
                $this->once()
            ],
            [
                $customer,
                false,
                false,
                [
                    'is_checkout_allowed' => false,
                    'is_login_allowed' => false,
                    'is_enabled' => false,
                    'is_company_blocked' => false,
                    'has_customer_company' => false,
                    'is_storefront_registration_allowed' => false
                ],
                $this->once()
            ],
            [
                $customer,
                true,
                false,
                [
                    'is_checkout_allowed' => true,
                    'is_login_allowed' => false,
                    'is_enabled' => false,
                    'is_company_blocked' => false,
                    'has_customer_company' => false,
                    'is_storefront_registration_allowed' => false
                ],
                $this->once()
            ],
            [
                $customer,
                false,
                true,
                [
                    'is_checkout_allowed' => false,
                    'is_login_allowed' => true,
                    'is_enabled' => false,
                    'is_company_blocked' => false,
                    'has_customer_company' => false,
                    'is_storefront_registration_allowed' => false
                ],
                $this->once()
            ],
            [null, false, false, [], $this->never()]
        ];
    }
}
