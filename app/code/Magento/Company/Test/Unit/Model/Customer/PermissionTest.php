<?php

namespace Magento\Company\Test\Unit\Model\Customer;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;

/**
 * Class PermissionTest.
 */
class PermissionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Company\Model\Customer\Permission
     */
    private $permission;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyManagement = $this->createMock(
            \Magento\Company\Api\CompanyManagementInterface::class
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->authorization = $this->createMock(
            \Magento\Company\Api\AuthorizationInterface::class
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->permission = $objectManagerHelper->getObject(
            \Magento\Company\Model\Customer\Permission::class,
            [
                'companyManagement' => $this->companyManagement,
                'customerRepository' => $this->customerRepository,
                'authorization' => $this->authorization,
            ]
        );
    }

    /**
     * Test isCheckoutAllowed method.
     *
     * @param int $status
     * @param $isNegotiableQuoteActive
     * @param string $resource
     * @param bool $isAllowed
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $counter
     * @param bool $expectedResult
     * @dataProvider isCheckoutAllowedDataProvider
     */
    public function testIsCheckoutAllowed(
        $status,
        $isNegotiableQuoteActive,
        $resource,
        $isAllowed,
        \PHPUnit\Framework\MockObject\Matcher\InvokedCount $counter,
        $expectedResult
    ) {
        $customerId = 1;
        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $company = $this->createMock(
            \Magento\Company\Api\Data\CompanyInterface::class
        );
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->companyManagement->expects($this->atLeastOnce())->method('getByCustomerId')->willReturn($company);
        $company->expects($this->once())->method('getStatus')->willReturn($status);
        $this->authorization->expects($counter)
            ->method('isAllowed')
            ->with($resource)
            ->willReturn($isAllowed);

        $this->assertEquals($expectedResult, $this->permission->isCheckoutAllowed($customer, $isNegotiableQuoteActive));
    }

    /**
     * Data provider for isCheckoutAllowed method.
     *
     * @return array
     */
    public function isCheckoutAllowedDataProvider()
    {
        return [
            [CompanyInterface::STATUS_BLOCKED, true, 'Magento_NegotiableQuote::checkout', false, $this->never(), false],
            [CompanyInterface::STATUS_APPROVED, false, 'Magento_Sales::place_order', false, $this->once(), false],
            [CompanyInterface::STATUS_APPROVED, true, 'Magento_NegotiableQuote::checkout', true, $this->once(), true],
        ];
    }

    /**
     * Data provider for isCompanyLocked method.
     *
     * @return array
     */
    public function isCompanyLockedDataProvider()
    {
        return [
            [CompanyInterface::STATUS_REJECTED, true],
            [CompanyInterface::STATUS_PENDING, true],
            [CompanyInterface::STATUS_APPROVED, false],
        ];
    }

    /**
     * Test isLoginAllowed method.
     *
     * @param int $status
     * @param bool $expectedResult
     * @return void
     * @dataProvider isLoginAllowedDataProvider
     */
    public function testIsLoginAllowed($status, $expectedResult)
    {
        $customerId = 1;
        $customer = $this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $company = $this->createMock(
            \Magento\Company\Api\Data\CompanyInterface::class
        );
        $extensionAttributes = $this->getMockForAbstractClass(
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
        $customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->willReturn($company);
        $company->expects($this->once())->method('getStatus')->willReturn(CompanyInterface::STATUS_APPROVED);
        $customer->expects($this->exactly(3))->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->exactly(2))
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getStatus')->willReturn($status);

        $this->assertEquals($expectedResult, $this->permission->isLoginAllowed($customer));
    }

    /**
     * Data provider for isLoginAllowed method.
     *
     * @return array
     */
    public function isLoginAllowedDataProvider()
    {
        return [
            [CompanyCustomerInterface::STATUS_INACTIVE, false],
            [CompanyCustomerInterface::STATUS_ACTIVE, true],
        ];
    }
}
