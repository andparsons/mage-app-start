<?php
namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\CompanyCredit\Model\CreditCurrencyHistory;
use Magento\CompanyCredit\Model\CreditLimit;
use Magento\CompanyCredit\Model\CreditLimitFactory;
use Magento\CompanyCredit\Model\CreditLimitHistory;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\CompanyCredit\Model\ResourceModel\CreditLimit as CreditLimitResource;
use Magento\CompanyCredit\Model\SaveHandler;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for SaveHandler class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var CreditLimitFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitFactoryMock;

    /**
     * @var CreditLimitResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitResourceMock;

    /**
     * @var CreditLimitHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditLimitHistoryMock;

    /**
     * @var CreditCurrencyHistory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditCurrencyHistoryMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->creditLimitFactoryMock = $this->getMockBuilder(CreditLimitFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitResourceMock = $this->getMockBuilder(CreditLimitResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditLimitHistoryMock = $this->getMockBuilder(CreditLimitHistory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditCurrencyHistoryMock = $this->getMockBuilder(CreditCurrencyHistory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->saveHandler = (new ObjectManager($this))->getObject(
            SaveHandler::class,
            [
                'creditLimitFactory' => $this->creditLimitFactoryMock,
                'creditLimitResource' => $this->creditLimitResourceMock,
                'creditLimitHistory' => $this->creditLimitHistoryMock,
                'creditCurrencyHistory' => $this->creditCurrencyHistoryMock,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    /**
     * Test `execute` method.
     *
     * @return void
     */
    public function testExecute()
    {
        $credit = $this->buildCreditMock([]);
        $originCredit = $this->buildCreditMock([]);

        $this->creditLimitFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($originCredit);

        $this->creditLimitResourceMock->expects($this->once())
            ->method('save')
            ->with($credit);

        $this->creditLimitHistoryMock->expects($this->once())
            ->method('logUpdateItem')
            ->with($credit, $originCredit);

        $this->assertEquals(
            $credit,
            $this->saveHandler->execute($credit)
        );
    }

    /**
     * Test `execute` method with zero credit limit.
     *
     * @return void
     */
    public function testExecuteWithZeroCreditLimit()
    {
        $creditLimit = 0;

        $credit = $this->buildCreditMock([]);
        $originCredit = $this->buildCreditMock([]);
        $credit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn($creditLimit);
        $originCredit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn(null);

        $this->creditLimitFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($originCredit);

        $credit->expects($this->atLeastOnce())
            ->method('setCreditLimit')
            ->with(null);

        $this->creditLimitResourceMock->expects($this->once())
            ->method('save')
            ->with($credit);

        $this->creditLimitHistoryMock->expects($this->once())
            ->method('logUpdateItem')
            ->with($credit, $originCredit);

        $this->assertEquals(
            $credit,
            $this->saveHandler->execute($credit)
        );
    }

    /**
     * Test `execute` method with change currency.
     *
     * @return void
     */
    public function testExecuteWithChangeCurrency()
    {
        $id = 1;
        $limit = 2;
        $fromCurrency = 'EUR';
        $toCurrency = 'USD';
        $rate = 3;

        $credit = $this->buildCreditMock([]);
        $originCredit = $this->buildCreditMock([]);
        $newCredit = $this->getMockBuilder(CreditLimit::class)
            ->setMethods([
                'setData', 'getId', 'setId', 'setBalance', 'setCurrencyRate', 'getCreditLimit', 'setCreditLimit'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $newCredit->expects($this->atLeastOnce())
            ->method('setData')
            ->willReturnSelf();
        $originCredit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn($limit);

        $credit->expects($this->atLeastOnce())
            ->method('getCurrencyCode')
            ->willReturn($toCurrency);
        $originCredit->expects($this->atLeastOnce())
            ->method('getCurrencyCode')
            ->willReturn($fromCurrency);
        $originCredit->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);

        $this->creditLimitFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $originCredit,
                $newCredit
            );

        $currency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currency->expects($this->once())
            ->method('getRate')
            ->with($toCurrency)
            ->willReturn($rate);
        $this->priceCurrencyMock->expects($this->once())
            ->method('getCurrency')
            ->with(null, $fromCurrency)
            ->willReturn($currency);

        $newCredit->expects($this->once())
            ->method('setCreditLimit')
            ->with($limit * $rate)
            ->willReturnSelf();
        $newCredit->expects($this->once())
            ->method('setBalance')
            ->willReturnSelf();
        $newCredit->expects($this->once())
            ->method('setCurrencyRate')
            ->willReturnSelf();

        $this->creditLimitResourceMock->expects($this->once())
            ->method('save')
            ->with($newCredit);

        $this->creditCurrencyHistoryMock->expects($this->once())
            ->method('update');

        $this->creditLimitHistoryMock->expects($this->once())
            ->method('logUpdateItem')
            ->with($newCredit, $originCredit);

        $this->assertEquals(
            $newCredit,
            $this->saveHandler->execute($credit)
        );
    }

    /**
     * Test `execute` method with credit allocation.
     *
     * @return void
     */
    public function testExecuteWithAllocation()
    {
        $creditLimit = 10;

        $credit = $this->buildCreditMock([]);
        $originCredit = $this->buildCreditMock([]);

        $credit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn($creditLimit);
        $originCredit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn(null);

        $this->creditLimitFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($originCredit);

        $this->creditLimitResourceMock->expects($this->once())
            ->method('save')
            ->with($credit);

        $this->creditLimitHistoryMock->expects($this->once())
            ->method('logUpdateItem')
            ->with($credit, $originCredit);
        $this->creditLimitHistoryMock->expects($this->once())
            ->method('logCredit')
            ->with($credit, HistoryInterface::TYPE_ALLOCATED, 0);

        $this->assertEquals(
            $credit,
            $this->saveHandler->execute($credit)
        );
    }

    /**
     * Test `execute` method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not save company credit limit
     */
    public function testExecuteWithException()
    {
        $credit = $this->buildCreditMock([]);
        $originCredit = $this->buildCreditMock([]);

        $this->creditLimitFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($originCredit);

        $this->creditLimitResourceMock->expects($this->once())
            ->method('save')
            ->with($credit)
            ->willThrowException(new \Exception());

        $this->creditLimitHistoryMock->expects($this->never())
            ->method('logUpdateItem')
            ->with($credit, $originCredit);

        $this->saveHandler->execute($credit);
    }

    /**
     * Build mock for credit entity.
     *
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildCreditMock(array $data)
    {
        $credit = $this->getMockBuilder(CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $credit->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn($data);

        return $credit;
    }
}
