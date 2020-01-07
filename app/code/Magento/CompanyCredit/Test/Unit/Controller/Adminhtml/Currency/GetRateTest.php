<?php

namespace Magento\CompanyCredit\Test\Unit\Controller\Adminhtml\Currency;

/**
 * Class GetRateTest.
 */
class GetRateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\CompanyCredit\Controller\Adminhtml\Currency\GetRate
     */
    private $getRate;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->resultFactory = $this->createPartialMock(
            \Magento\Framework\Controller\ResultFactory::class,
            ['create']
        );
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->getRate = $objectManager->getObject(
            \Magento\CompanyCredit\Controller\Adminhtml\Currency\GetRate::class,
            [
                'priceCurrency' => $this->priceCurrency,
                'logger' => $this->logger,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $creditCurrency = 'EUR';
        $newCurrency = 'USD';
        $currencySymbol = '$';
        $rate = 1.4;
        $this->request->expects($this->at(0))->method('getParam')->with('currency_from')->willReturn($creditCurrency);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $targetCurrency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->priceCurrency->expects($this->exactly(2))->method('getCurrency')
            ->withConsecutive([], [null, $newCurrency])
            ->willReturnOnConsecutiveCalls($currency, $targetCurrency);
        $currency->expects($this->once())->method('getCurrencyRates')
            ->with($creditCurrency, [$newCurrency])->willReturn([$newCurrency => $rate]);
        $targetCurrency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'success',
                'currency_rate' => '1.4000',
                'currency_symbol' => $currencySymbol,
            ]
        )->willReturnSelf();
        $this->assertEquals($result, $this->getRate->execute());
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $creditCurrency = 'EUR';
        $newCurrency = 'USD';
        $this->request->expects($this->at(0))->method('getParam')->with('currency_from')->willReturn($creditCurrency);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $this->priceCurrency->expects($this->once())->method('getCurrency')->willThrowException($exception);
        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'error' => __('Something went wrong. Please try again later.')
            ]
        )->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->assertEquals($result, $this->getRate->execute());
    }

    /**
     * Test for method execute with localized exception.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $exceptionMessage = 'Exception Message';
        $creditCurrency = 'EUR';
        $newCurrency = 'USD';
        $this->request->expects($this->at(0))->method('getParam')->with('currency_from')->willReturn($creditCurrency);
        $this->request->expects($this->at(1))->method('getParam')->with('currency_to')->willReturn($newCurrency);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($result);
        $this->priceCurrency->expects($this->once())->method('getCurrency')->willThrowException(
            new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage))
        );
        $result->expects($this->once())->method('setData')->with(
            [
                'status' => 'error',
                'error' => $exceptionMessage
            ]
        )->willReturnSelf();
        $this->assertEquals($result, $this->getRate->execute());
    }
}
