<?php

namespace Magento\CompanyCredit\Test\Unit\Block\Adminhtml\Company\Edit;

/**
 * Class CreditBalanceTest.
 */
class CreditBalanceTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $credit;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Block\Adminhtml\Company\Edit\CreditBalance
     */
    private $creditBalance;

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
        $this->websiteCurrency = $this->createMock(
            \Magento\CompanyCredit\Model\WebsiteCurrency::class
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();
        $this->credit = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditBalance = $objectManager->getObject(
            \Magento\CompanyCredit\Block\Adminhtml\Company\Edit\CreditBalance::class,
            [
                'creditLimit' => $this->creditLimit,
                'creditDataProvider' => $this->creditDataProvider,
                'priceFormatter' => $this->priceFormatter,
                'websiteCurrency' => $this->websiteCurrency,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test for method getOutstandingBalance.
     *
     * @return void
     */
    public function testGetOutstandingBalance()
    {
        $value = -5;
        $expectedValue = $this->prepareMocks($value);
        $this->credit->expects($this->once())->method('getBalance')->willReturn($value);
        $this->assertEquals($expectedValue, $this->creditBalance->getOutstandingBalance());
    }

    /**
     * Test for method getCreditLimit.
     *
     * @return void
     */
    public function testGetCreditLimit()
    {
        $value = 20;
        $expectedValue = $this->prepareMocks($value);
        $this->credit->expects($this->once())->method('getCreditLimit')->willReturn($value);
        $this->assertEquals($expectedValue, $this->creditBalance->getCreditLimit());
    }

    /**
     * Test for method getAvailableCredit.
     *
     * @return void
     */
    public function testGetAvailableCredit()
    {
        $value = 15;
        $expectedValue = $this->prepareMocks($value);
        $this->credit->expects($this->once())->method('getAvailableLimit')->willReturn($value);
        $this->assertEquals($expectedValue, $this->creditBalance->getAvailableCredit());
    }

    /**
     * Test for method isOutstandingBalanceNegative.
     *
     * @return void
     */
    public function testIsOutstandingBalanceNegative()
    {
        $companyId = 1;
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('id')->willReturn($companyId);
        $this->creditDataProvider->expects($this->once())->method('get')->with($companyId)->willReturn($this->credit);
        $this->credit->expects($this->once())->method('getBalance')->willReturn(-1);
        $this->assertTrue($this->creditBalance->isOutstandingBalanceNegative());
    }

    /**
     * Test for method isOutstandingBalanceNegative with exception.
     *
     * @return void
     */
    public function testIsOutstandingBalanceNegativeWithException()
    {
        $companyId = 1;
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('id')->willReturn($companyId);
        $this->creditDataProvider->expects($this->once())->method('get')->with($companyId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->assertFalse($this->creditBalance->isOutstandingBalanceNegative());
    }

    /**
     * Prepare mocks and return expected value.
     *
     * @param int $value
     * @return string
     */
    private function prepareMocks($value)
    {
        $companyId = 1;
        $currencyCode = 'USD';
        $expectedValue = '$' . $value . '.00';
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('id')->willReturn($companyId);
        $this->creditDataProvider->expects($this->once())->method('get')
            ->with($companyId)->willReturn($this->credit);
        $this->credit->expects($this->once())->method('getCurrencyCode')->willReturn($currencyCode);
        $this->websiteCurrency->expects($this->once())
            ->method('getCurrencyByCode')->with($currencyCode)->willReturn($currencyCode);
        $this->priceFormatter->expects($this->once())->method('format')
            ->with(
                $value,
                false,
                \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                null,
                $currencyCode
            )->willReturn($expectedValue);
        return $expectedValue;
    }
}
