<?php
namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\CompanyCredit\Api\CreditLimitRepositoryInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory;
use Magento\CompanyCredit\Model\CreditCurrency;
use Magento\CompanyCredit\Model\CreditCurrencyHistory;
use Magento\CompanyCredit\Model\CreditLimitHistory;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\CompanyCredit\Model\WebsiteCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CreditCurrencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreditLimitInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitFactory;

    /**
     * @var CreditLimitRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitRepository;

    /**
     * @var WebsiteCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCurrency;

    /**
     * @var CreditCurrencyHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditCurrencyHistory;

    /**
     * @var CreditLimitHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitHistory;

    /**
     * @var CreditCurrency
     */
    private $creditCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->creditLimitFactory = $this->createPartialMock(
            CreditLimitInterfaceFactory::class,
            ['create']
        );
        $this->creditLimitRepository = $this->createMock(
            CreditLimitRepositoryInterface::class
        );
        $this->websiteCurrency = $this->createMock(
            WebsiteCurrency::class
        );
        $this->creditCurrencyHistory = $this->createMock(
            CreditCurrencyHistory::class
        );
        $this->creditLimitHistory = $this->createMock(
            CreditLimitHistory::class
        );

        $objectManager = new ObjectManager($this);
        $this->creditCurrency = $objectManager->getObject(
            CreditCurrency::class,
            [
                'creditLimitFactory' => $this->creditLimitFactory,
                'creditLimitRepository' => $this->creditLimitRepository,
                'websiteCurrency' => $this->websiteCurrency,
                'creditCurrencyHistory' => $this->creditCurrencyHistory,
                'creditLimitHistory' => $this->creditLimitHistory,
            ]
        );
    }

    /**
     * Test for change method.
     *
     * @return void
     */
    public function testChange()
    {
        $companyId = 1;
        $currencyRate = 1.5;
        $creditBalance = 50;
        $currentCreditLimitId = 2;
        $creditLimitId = 3;

        $oldCurrency = 'EUR';
        $companyCreditData = [
            CreditLimitInterface::CURRENCY_CODE => 'USD',
            CreditLimitInterface::CREDIT_LIMIT => 100,
        ];
        $currentCreditLimit = $this->getMockBuilder(CreditLimitInterface::class)
            ->setMethods(['getCompanyId', 'getBalance', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $creditLimit = $this->getMockBuilder(CreditLimitInterface::class)
            ->setMethods(['setData', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->websiteCurrency->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($companyCreditData[CreditLimitInterface::CURRENCY_CODE])->willReturn(true);
        $this->creditLimitFactory->expects($this->once())->method('create')->willReturn($creditLimit);
        $currentCreditLimit->expects($this->once())->method('getCompanyId')->willReturn($companyId);
        $currentCreditLimit->expects($this->once())->method('getBalance')->willReturn($creditBalance);
        $creditLimit->expects($this->once())->method('setData')->with(
            $companyCreditData + [
                CreditLimitInterface::COMPANY_ID => $companyId,
                CreditLimitInterface::BALANCE => $creditBalance * $currencyRate,
            ]
        )->willReturnSelf();
        $this->creditLimitRepository->expects($this->once())
            ->method('save')->with($creditLimit)->willReturn($creditLimit);
        $currentCreditLimit->expects($this->once())->method('getId')->willReturn($currentCreditLimitId);
        $creditLimit->expects($this->once())->method('getId')->willReturn($creditLimitId);
        $this->creditCurrencyHistory->expects($this->once())
            ->method('update')->with($currentCreditLimitId, $creditLimitId);
        $this->creditLimitRepository->expects($this->once())
            ->method('delete')->with($currentCreditLimit)->willReturn(true);
        $currentCreditLimit->expects($this->once())->method('getCurrencyCode')->willReturn($oldCurrency);
        $commentData = [
            'currency_from' => $oldCurrency,
            'currency_to' => $companyCreditData[CreditLimitInterface::CURRENCY_CODE],
            'currency_rate' => $currencyRate,
            'user_name' => 'user'
        ];
        $this->creditLimitHistory->expects($this->once())->method('prepareChangeCurrencyComment')
            ->with(
                $oldCurrency,
                $companyCreditData[CreditLimitInterface::CURRENCY_CODE],
                $currencyRate
            )->willReturn($commentData);
        $this->creditLimitHistory->expects($this->once())
            ->method('logUpdateCreditLimit')
            ->with($creditLimit, '', [HistoryInterface::COMMENT_TYPE_UPDATE_CURRENCY => $commentData]);
        $this->assertEquals(
            $creditLimit,
            $this->creditCurrency->change($currentCreditLimit, $companyCreditData, $currencyRate)
        );
    }

    /**
     * Test for change method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The selected currency is not available. Please select a different currency.
     */
    public function testChangeWithException()
    {
        $companyCreditData = [
            CreditLimitInterface::CURRENCY_CODE => 'USD',
        ];
        $creditLimit = $this->createMock(CreditLimitInterface::class);
        $this->websiteCurrency->expects($this->once())->method('isCreditCurrencyEnabled')
            ->with($companyCreditData[CreditLimitInterface::CURRENCY_CODE])->willReturn(false);
        $this->creditCurrency->change($creditLimit, $companyCreditData, 1.5);
    }
}
