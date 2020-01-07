<?php

namespace Magento\CompanyCredit\Test\Unit\Block\Adminhtml\Order;

/**
 * Unit test for block Message.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimit;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceFormatter;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;

    /**
     * @var \Magento\CompanyCredit\Block\Adminhtml\Order\Message
     */
    private $message;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditLimit = $this->createMock(
            \Magento\CompanyCredit\Api\Data\CreditLimitInterface::class
        );
        $this->creditDataProvider = $this->createMock(
            \Magento\CompanyCredit\Api\CreditDataProviderInterface::class
        );
        $this->priceFormatter = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );

        $this->order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $coreRegistry->expects($this->atLeastOnce())->method('registry')
            ->with('current_order')->willReturn($this->order);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->message = $objectManager->getObject(
            \Magento\CompanyCredit\Block\Adminhtml\Order\Message::class,
            [
                'creditLimit' => $this->creditLimit,
                'creditDataProvider' => $this->creditDataProvider,
                'priceFormatter' => $this->priceFormatter,
                'customerRepository' => $this->customerRepository,
                'companyRepository' => $this->companyRepository,
                '_coreRegistry' => $coreRegistry,
            ]
        );
    }

    /**
     * Test for method isPayOnAccountMethod.
     *
     * @return void
     */
    public function testIsPayOnAccountMethod()
    {
        $payment = $this->createMock(\Magento\Sales\Api\Data\OrderPaymentInterface::class);
        $this->order->expects($this->once())->method('getPayment')->willReturn($payment);
        $payment->expects($this->once())->method('getMethod')
            ->willReturn(\Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::METHOD_NAME);
        $this->assertTrue($this->message->isPayOnAccountMethod());
    }

    /**
     * Test for method formatPrice.
     *
     * @return void
     */
    public function testFormatPrice()
    {
        $price = 17;
        $currencyCode = 'USD';
        $expectedValue = '$' . $price . '.00';
        $credit = $this->prepareMocks(2);
        $credit->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);
        $credit->expects($this->once())->method('getId')->willReturn(1);
        $this->priceFormatter->expects($this->once())->method('format')
            ->with($price, false, 2, null, $currencyCode)->willReturn($expectedValue);
        $this->assertEquals($expectedValue, $this->message->formatPrice($price));
    }

    /**
     * Test for method formatPrice with exception.
     *
     * @return void
     */
    public function testFormatPriceWithException()
    {
        $customerId = 1;
        $price = 17;
        $expectedValue = '$' . $price . '.00';
        $this->order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->with($customerId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->priceFormatter->expects($this->once())->method('format')
            ->with($price, false, 2, null, null)->willReturn($expectedValue);
        $this->assertEquals($expectedValue, $this->message->formatPrice($price));
    }

    /**
     * Test for method formatPrice without credit.
     *
     * @return void
     */
    public function testFormatPriceWithoutCredit()
    {
        $price = 17;
        $expectedValue = '$' . $price . '.00';
        $credit = $this->prepareMocks(2);
        $credit->expects($this->once())->method('getId')->willReturn(0);
        $this->priceFormatter->expects($this->once())->method('format')
            ->with($price, false, 2, null, null)->willReturn($expectedValue);
        $this->assertEquals($expectedValue, $this->message->formatPrice($price));
    }

    /**
     * Test for method getCompanyName.
     *
     * @return void
     */
    public function testGetCompanyName()
    {
        $companyId = 2;
        $companyName = 'Some Company';
        $credit = $this->prepareMocks($companyId);
        $credit->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $credit->expects($this->once())->method('getId')->willReturn(1);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $this->assertEquals($companyName, $this->message->getCompanyName());
    }

    /**
     * Test for method getCompanyName with exception.
     *
     * @return void
     */
    public function testGetCompanyNameWithException()
    {
        $companyId = 2;
        $credit = $this->prepareMocks($companyId);
        $credit->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $credit->expects($this->once())->method('getId')->willReturn(1);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertEquals('', $this->message->getCompanyName());
    }

    /**
     * Prepare mocks for getCredit method and return credit mock.
     *
     * @param int $companyId
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareMocks($companyId)
    {
        $customerId = 1;
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository->expects($this->once())->method('getById')->with($customerId)->willReturn($customer);
        $customerExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setCompanyAttributes', 'getCompanyAttributes']
        );
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $customer->expects($this->once())->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $customerExtensionAttributes->expects($this->any())->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $companyAttributes->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $credit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $this->creditDataProvider->expects($this->once())->method('get')->with($companyId)->willReturn($credit);
        return $credit;
    }
}
