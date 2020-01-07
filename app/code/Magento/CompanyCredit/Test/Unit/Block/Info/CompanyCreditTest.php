<?php

namespace Magento\CompanyCredit\Test\Unit\Block\Info;

/**
 * Class CompanyCreditTest.
 */
class CompanyCreditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var \Magento\CompanyCredit\Block\Info\CompanyCredit
     */
    private $companyCredit;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->creditDataProvider = $this->createMock(
            \Magento\CompanyCredit\Api\CreditDataProviderInterface::class
        );
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyCredit = $objectManager->getObject(
            \Magento\CompanyCredit\Block\Info\CompanyCredit::class,
            [
                'creditDataProvider' => $this->creditDataProvider,
                'priceCurrency' => $this->priceCurrency,
                'customerRepository' => $this->customerRepository,
            ]
        );
    }

    /**
     * Test for getChargedAmount method.
     *
     * @return void
     */
    public function testGetChargedAmount()
    {
        $storeId = 1;
        $companyId = 2;
        $customerId = 3;
        $grandTotal = 12.5;
        $rate = 1.4;
        $creditCurrency = 'USD';
        $orderCurrency = 'EUR';
        $expectedResult = '$' . ($grandTotal * $rate);
        $info = $this->getMockForAbstractClass(
            \Magento\Payment\Model\InfoInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getOrder']
        );
        $this->companyCredit->setData('info', $info);
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $info->expects($this->exactly(3))->method('getOrder')->willReturn($order);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($grandTotal);
        $order->expects($this->once())->method('getBaseGrandTotal')->willReturn($grandTotal);
        $order->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $order->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepository->expects($this->once())->method('getById')->with($customerId)->willReturn($customer);
        $customerExtensionAttributes = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCompanyAttributes']
        );
        $customer->expects($this->exactly(4))
            ->method('getExtensionAttributes')->willReturn($customerExtensionAttributes);
        $companyAttributes = $this->createMock(
            \Magento\Company\Api\Data\CompanyCustomerInterface::class
        );
        $customerExtensionAttributes->expects($this->exactly(3))
            ->method('getCompanyAttributes')->willReturn($companyAttributes);
        $companyAttributes->expects($this->exactly(2))->method('getCompanyId')->willReturn($companyId);
        $creditData = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $this->creditDataProvider->expects($this->once())->method('get')->with($companyId)->willReturn($creditData);
        $creditData->expects($this->exactly(5))->method('getCurrencyCode')->willReturn($creditCurrency);
        $order->expects($this->once())->method('getOrderCurrencyCode')->willReturn($orderCurrency);
        $order->expects($this->once())->method('getBaseCurrencyCode')->willReturn($orderCurrency);
        $currency = $this->createPartialMock(
            \Magento\Directory\Model\Currency::class,
            ['getRate', 'convert']
        );
        $this->priceCurrency->expects($this->once())
            ->method('getCurrency')->with(true, $orderCurrency)->willReturn($currency);
        $currency->expects($this->once())->method('getRate')->with($creditCurrency)->willReturn($rate);
        $currency->expects($this->once())
            ->method('convert')->with($grandTotal, $creditCurrency)->willReturn($grandTotal * $rate);
        $this->priceCurrency->expects($this->once())->method('format')->with(
            $grandTotal * $rate,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId,
            $creditCurrency
        )->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->companyCredit->getChargedAmount());
    }

    /**
     * Test for getChargedAmount method with empty company id.
     *
     * @return void
     */
    public function testGetChargedAmountWithEmptyCompanyId()
    {
        $storeId = 1;
        $grandTotal = 12.5;
        $expectedResult = '$' . $grandTotal;
        $info = $this->getMockForAbstractClass(
            \Magento\Payment\Model\InfoInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getOrder']
        );
        $this->companyCredit->setData('info', $info);
        $order = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($grandTotal);
        $order->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $info->expects($this->exactly(2))->method('getOrder')->willReturn($order);
        $creditData = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $this->creditDataProvider->expects($this->once())->method('get')->with(0)->willReturn($creditData);
        $creditData->expects($this->exactly(2))->method('getCurrencyCode')->willReturn(null);
        $this->priceCurrency->expects($this->once())->method('format')->with(
            $grandTotal,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $storeId,
            null
        )->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->companyCredit->getChargedAmount());
    }
}
