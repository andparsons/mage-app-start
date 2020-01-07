<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Unit test for HistoryHydrator model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HistoryHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryHydrator
     */
    private $historyHydrator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectHelper = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->historyHydrator = $objectManager->getObject(
            \Magento\CompanyCredit\Model\HistoryHydrator::class,
            [
                'userContext' => $this->userContext,
                'priceCurrency' => $this->priceCurrency,
                'objectHelper' => $this->objectHelper,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * Data provider for testLogCredit.
     *
     * @return array
     */
    public function hydrateProvider()
    {
        return [
            ['USD', 'USD', 1, 1, 1, \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER, 1, 0, 1],
            ['EUR', 'EUR', 1.3, 0, 0, 0, 1, 1, 0],
            [null, 'USD', 1, 1, 0, 0, 2, 1, 0],
        ];
    }

    /**
     * Test for method logCredit.
     *
     * @param string $currencyCode
     * @param string $currencyOperation
     * @param float|int $currencyRate
     * @param int $currencyRateCalls
     * @param int $userId
     * @param int $userType
     * @param int $currencyCodeCalls
     * @param int $userContextCalls
     * @param int $userPreset
     * @return void
     * @dataProvider hydrateProvider
     */
    public function testHydrate(
        $currencyCode,
        $currencyOperation,
        $currencyRate,
        $currencyRateCalls,
        $userId,
        $userType,
        $currencyCodeCalls,
        $userContextCalls,
        $userPreset
    ) {
        $data = [
            'status' =>  \Magento\CompanyCredit\Model\HistoryInterface::TYPE_PURCHASED,
            'amount' => 10,
            'currency' => $currencyCode,
            'comment' => 'Some comment',
            'systemComments' => ['order' => '00001'],
            'purchaseOrder' => 'O123',
        ];
        $data['options'] = new \Magento\Framework\DataObject(
            [
                'purchaseOrder' => 'O123',
                'order_increment' => '00001',
                'currency_display' => 'RUB',
                'currency_base' => 'EUR'
            ]
        );
        $creditId = 1;
        $creditBalance = -15;
        $creditLimit = 75;
        $availableLimit = 60;
        $creditCurrency = 'USD';
        $credit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $credit->expects($this->once())->method('getId')->willReturn($creditId);
        $credit->expects($this->once())->method('getBalance')->willReturn($creditBalance);
        $credit->expects($this->once())->method('getCreditLimit')->willReturn($creditLimit);
        $credit->expects($this->once())->method('getAvailableLimit')->willReturn($availableLimit);
        $credit->expects($this->exactly($currencyCodeCalls))->method('getCurrencyCode')->willReturn($creditCurrency);
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->objectHelper->expects($this->once())->method('populateWithArray')
            ->with($history, $data['options']->getData(), \Magento\CompanyCredit\Model\HistoryInterface::class)
            ->willReturnSelf();
        $history->expects($this->once())->method('unsetData')
            ->with(\Magento\CompanyCredit\Model\HistoryInterface::HISTORY_ID)->willReturnSelf();
        $history->expects($this->once())->method('setCompanyCreditId')->with($creditId)->willReturnSelf();
        $history->expects($this->once())->method('setBalance')->with($creditBalance)->willReturnSelf();
        $history->expects($this->once())->method('setCreditLimit')->with($creditLimit)->willReturnSelf();
        $history->expects($this->once())->method('setAvailableLimit')->with($availableLimit)->willReturnSelf();
        $history->expects($this->once())->method('setCurrencyCredit')->with($creditCurrency)->willReturnSelf();
        $history->expects($this->once())->method('setType')->with($data['status'])->willReturnSelf();
        $history->expects($this->once())->method('setAmount')->with($data['amount'])->willReturnSelf();
        $history->expects($this->once())->method('setCurrencyOperation')
            ->with($data['options']->getData('currency_display'))->willReturnSelf();
        $this->serializer->expects($this->once())->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $history->expects($this->once())->method('setComment')
            ->with(json_encode(['custom' => $data['comment']] + ['system' => $data['systemComments']]))
            ->willReturnSelf();
        $history->expects($this->exactly($userContextCalls))->method('setUserId')->with($userId)->willReturnSelf();
        $history->expects($this->exactly($userContextCalls))->method('setUserType')->with($userType)->willReturnSelf();
        $history->expects($this->once())->method('getUserId')->willReturn(1);
        $history->expects($this->once())->method('getUserType')->willReturn($userPreset);
        $history->expects($this->once())->method('setRate')->with(1)->willReturnSelf();
        $history->expects($this->once())->method('setRateCredit')->with($currencyRate)->willReturnSelf();
        $history->expects($this->once())->method('getCurrencyCredit')->willReturn($creditCurrency);
        $history->expects($this->once())->method('getCurrencyOperation')->willReturn($currencyOperation);
        $this->userContext->expects($this->exactly($userContextCalls))->method('getUserId')->willReturn($userId);
        $this->userContext->expects($this->exactly($userContextCalls))->method('getUserType')->willReturn($userType);
        $currency = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()->getMock();
        $this->priceCurrency->expects($this->exactly(1 + $currencyRateCalls))
            ->method('getCurrency')->with(null, $data['options']->getCurrencyBase())->willReturn($currency);
        $currency->expects($this->exactly(1 + $currencyRateCalls))->method('getRate')
            ->with($creditCurrency)->willReturn($currencyRate);
        $this->historyHydrator->hydrate(
            $history,
            $credit,
            $data['status'],
            $data['amount'],
            $data['currency'],
            $data['comment'],
            $data['systemComments'],
            $data['options']
        );
    }
}
