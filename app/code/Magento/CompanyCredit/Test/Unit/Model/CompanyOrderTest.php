<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Unit test for Company Order.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyStatus;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyOrder
     */
    private $companyOrder;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $this->companyStatus = $this->createMock(
            \Magento\CompanyCredit\Model\CompanyStatus::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyOrder = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CompanyOrder::class,
            [
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository,
                'companyStatus' => $this->companyStatus,
            ]
        );
    }

    /**
     * Test for method getCompanyIdByOrder.
     *
     * @return void
     */
    public function testGetCompanyIdByOrder()
    {
        $companyId = 1;
        $customerId = 2;
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $order->expects($this->exactly(2))->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
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
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $this->assertEquals($companyId, $this->companyOrder->getCompanyIdByOrder($order));
    }

    /**
     * Test for method getCompanyIdByOrder with deleted customer.
     *
     * @return void
     */
    public function testGetCompanyIdByOrderWithDeletedCustomer()
    {
        $companyId = 1;
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $order->expects($this->exactly(2))->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $order->expects($this->once())->method('getCustomerId')->willReturn(null);
        $orderPayment->expects($this->once())
            ->method('getAdditionalInformation')->with('company_id')->willReturn($companyId);
        $this->assertEquals($companyId, $this->companyOrder->getCompanyIdByOrder($order));
    }

    /**
     * Test for method getCompanyIdForRefund.
     *
     * @return void
     */
    public function testGetCompanyIdForRefund()
    {
        $companyId = 1;
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $order->expects($this->exactly(2))->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $order->expects($this->once())->method('getCustomerId')->willReturn(null);
        $orderPayment->expects($this->once())
            ->method('getAdditionalInformation')->with('company_id')->willReturn($companyId);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyStatus->expects($this->once())->method('isRefundAvailable')->with($companyId)->willReturn(true);
        $this->assertEquals($companyId, $this->companyOrder->getCompanyIdForRefund($order));
    }

    /**
     * Test for method getCompanyIdForRefund with deleted company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetCompanyIdForRefundWithDeletedCompany()
    {
        $companyId = null;
        $customerId = 2;
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $order->expects($this->once())->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $order->expects($this->exactly(2))->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->willReturn($customer);
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
        $customer->expects($this->atLeastOnce())
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->atLeastOnce())
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->atLeastOnce())->method('getCompanyId')->willReturn($companyId);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals($companyId, $this->companyOrder->getCompanyIdForRefund($order));
    }

    /**
     * Test for method getCompanyIdForRefund with rejected company.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetCompanyIdForRefundWithRejectedCompany()
    {
        $companyId = 1;
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderPayment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $order->expects($this->exactly(2))->method('getPayment')->willReturn($orderPayment);
        $orderPayment->expects($this->once())
            ->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $order->expects($this->once())->method('getCustomerId')->willReturn(null);
        $orderPayment->expects($this->once())
            ->method('getAdditionalInformation')->with('company_id')->willReturn($companyId);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyStatus->expects($this->once())->method('isRefundAvailable')->with($companyId)->willReturn(false);
        $this->assertEquals($companyId, $this->companyOrder->getCompanyIdForRefund($order));
    }
}
