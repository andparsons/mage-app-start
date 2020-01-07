<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class CompanyCreditPaymentConfigProviderTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyCreditPaymentConfigProviderTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\CreditCheckoutData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditCheckoutData;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider
     */
    private $configProvider;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->createMock(
            \Magento\Authorization\Model\UserContextInterface::class
        );
        $this->customerRepository = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->creditDataProvider = $this->createMock(
            \Magento\CompanyCredit\Api\CreditDataProviderInterface::class
        );
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->companyRepository = $this->createMock(
            \Magento\Company\Api\CompanyRepositoryInterface::class
        );
        $this->priceCurrency = $this->createMock(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        );
        $this->context = $this->createMock(
            \Magento\Framework\App\Action\Context::class
        );
        $this->websiteCurrency = $this->createMock(
            \Magento\CompanyCredit\Model\WebsiteCurrency::class
        );
        $this->creditCheckoutData = $this->createMock(
            \Magento\CompanyCredit\Model\CreditCheckoutData::class
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configProvider = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider::class,
            [
                'userContext' => $this->userContext,
                'customerRepository' => $this->customerRepository,
                'creditDataProvider' => $this->creditDataProvider,
                'quoteRepository' => $this->quoteRepository,
                'companyRepository' => $this->companyRepository,
                'priceCurrency' => $this->priceCurrency,
                'context' => $this->context,
                'websiteCurrency' => $this->websiteCurrency,
                'creditCheckoutData' => $this->creditCheckoutData
            ]
        );
    }

    /**
     * Test for method getConfig.
     *
     * @param \PHPUnit\Framework\MockObject\Stub\ReturnStub|\PHPUnit\Framework\MockObject\Stub\Exception $quoteResult
     * @param int $negotiableInvocationsCount
     * @param \PHPUnit_Framework_MockObject_MockObject $quote
     * @return void
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(
        $quoteResult,
        $negotiableInvocationsCount,
        \PHPUnit_Framework_MockObject_MockObject $quote
    ) {
        $userId = 1;
        $companyId = 2;
        $quoteId = 3;
        $availableLimit = 50;
        $exceedLimit = true;
        $quoteTotal = 750;
        $companyName = 'Company Name';
        $creditCurrency = 'USD';
        $isOrderPlaceEnabled = true;
        $currencyConvertedRate = 1;
        $this->creditCheckoutData->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $this->userContext->expects($this->any())->method('getUserId')->willReturn($userId);
        $creditData = $this->createMock(\Magento\CompanyCredit\Api\Data\CreditDataInterface::class);
        $this->creditDataProvider->expects($this->once())->method('get')->with($companyId)->willReturn($creditData);
        $creditData->expects($this->exactly(7))->method('getCurrencyCode')->willReturn($creditCurrency);
        $this->quoteRepository->expects($this->once())
            ->method('getActiveForCustomer')->with($userId)->will($quoteResult);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->context->expects($this->exactly($negotiableInvocationsCount))
            ->method('getRequest')->willReturn($request);
        $request->expects($this->exactly($negotiableInvocationsCount))
            ->method('getParam')->with('negotiableQuoteId')->willReturn($quoteId);
        $this->quoteRepository->expects($this->exactly($negotiableInvocationsCount))
            ->method('get')->with($quoteId)->willReturn($quote);
        $creditData->expects($this->exactly(3))->method('getAvailableLimit')->willReturn($availableLimit);
        $creditData->expects($this->once())->method('getExceedLimit')->willReturn($exceedLimit);
        $quote->expects($this->exactly(2))->method('getBaseGrandTotal')->willReturn($quoteTotal);
        $company = $this->createMock(\Magento\Company\Api\Data\CompanyInterface::class);
        $this->companyRepository->expects($this->once())->method('get')->with($companyId)->willReturn($company);
        $company->expects($this->once())->method('getCompanyName')->willReturn($companyName);
        $creditCurrencyMock = $this->createMock(\Magento\Directory\Model\Currency::class);
        $this->websiteCurrency->expects($this->once())->method('getCurrencyByCode')
            ->with($creditCurrency)->willReturn($creditCurrencyMock);
        $this->creditCheckoutData->expects($this->once())->method('getGrandTotalInCreditCurrency')
            ->willReturn($quoteTotal);
        $this->creditCheckoutData->expects($this->once())->method('isBaseCreditCurrencyRateEnabled')
            ->willReturn($isOrderPlaceEnabled);
        $this->creditCheckoutData->expects($this->once())->method('getCurrencyConvertedRate')
            ->willReturn($currencyConvertedRate);
        $this->creditCheckoutData->expects($this->exactly(3))->method('formatPrice')
            ->withConsecutive(
                [$availableLimit, $creditCurrencyMock],
                [$quoteTotal, $creditCurrencyMock],
                [$quoteTotal - $availableLimit, $creditCurrencyMock]
            )->willReturnOnConsecutiveCalls(
                '$' . $availableLimit,
                '$' . $quoteTotal,
                '$' . ($quoteTotal - $availableLimit)
            );

        $expectedResult = [
            'payment' => [
                'companycredit' => [
                    'limit' => $availableLimit,
                    'exceedLimit' => $exceedLimit,
                    'limitFormatted' => '$' . $availableLimit,
                    'quoteTotalFormatted' => '$' . ($quoteTotal),
                    'exceededAmountFormatted' => '$' . ($quoteTotal - $availableLimit),
                    'currency' => $creditCurrency,
                    'rate' => $currencyConvertedRate,
                    'companyName' => $companyName,
                    'isBaseCreditCurrencyRateEnabled' => true,
                    'priceFormatPattern' => null,
                    'baseRate' => null
                ]
            ]
        ];
        $this->assertEquals($expectedResult, $this->configProvider->getConfig());
    }

    /**
     * Test for method getConfig with empty company ID.
     *
     * @return void
     */
    public function testGetConfigWithEmptyCompanyId()
    {
        $this->creditCheckoutData->expects($this->once())->method('getCompanyId')->willReturn(null);
        $this->assertEquals([], $this->configProvider->getConfig());
    }

    /**
     * Data provider for testGetConfig.
     *
     * @return array
     */
    public function getConfigDataProvider()
    {
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getQuoteCurrencyCode', 'getGrandTotal', 'getBaseCurrencyCode', 'getBaseGrandTotal']
        );
        return [
            [
                $this->returnValue($quote),
                0,
                $quote,
            ],
            [
                $this->throwException(new \Magento\Framework\Exception\NoSuchEntityException()),
                1,
                clone $quote,
            ]
        ];
    }
}
