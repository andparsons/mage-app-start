<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\CompanyCredit\Model\CreditLimit;
use Magento\CompanyCredit\Model\HistoryInterface;

/**
 * Unit test for CreditLimitHistory model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditLimitHistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepository;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyFactory;

    /**
     * @var \Magento\CompanyCredit\Model\Creator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creator;

    /**
     * @var \Magento\CompanyCredit\Model\HistoryHydrator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyHydrator;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitHistory
     */
    private $creditLimitHistory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyRepository = $this->getMockBuilder(\Magento\CompanyCredit\Model\HistoryRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyFactory = $this->getMockBuilder(\Magento\CompanyCredit\Model\HistoryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->creator = $this->getMockBuilder(\Magento\CompanyCredit\Model\Creator::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyHydrator = $this->getMockBuilder(\Magento\CompanyCredit\Model\HistoryHydrator::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditLimitHistory = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditLimitHistory::class,
            [
                'historyRepository' => $this->historyRepository,
                'userContext' => $this->userContext,
                'companyRepository' => $this->companyRepository,
                'creator' => $this->creator,
                'historyFactory' => $this->historyFactory,
                'historyHydrator' => $this->historyHydrator
            ]
        );
    }

    /**
     * Test for method logCredit.
     *
     * @param array $arguments
     * @return void
     * @dataProvider logCreditDataProvider
     */
    public function testLogCredit($arguments)
    {
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->historyHydrator->expects($this->once())->method('hydrate')->willReturn($history);
        $this->historyRepository->expects($this->once())->method('save')->with($history);

        $this->creditLimitHistory->logCredit(...$arguments);
    }

    /**
     * Data provider for testLogCredit.
     *
     * @return array
     */
    public function logCreditDataProvider()
    {
        $creditLimit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        return [
            [[$creditLimit, 1, 100.00, 'USD', 'comment', [], new \Magento\Framework\DataObject()]],
            [[$creditLimit, 1, 200, 'USD', 'comment', [], null]]
        ];
    }

    /**
     * Test for method logUpdateCreditLimit.
     *
     * @return void
     */
    public function testLogUpdateCreditLimit()
    {
        $credit = $this->getMockBuilder(\Magento\CompanyCredit\Api\Data\CreditLimitInterface::class)
            ->disableOriginalConstructor()->getMock();
        $history = $this->getMockBuilder(\Magento\CompanyCredit\Model\History::class)
            ->disableOriginalConstructor()->getMock();
        $this->historyFactory->expects($this->once())->method('create')->willReturn($history);
        $this->historyHydrator->expects($this->once())->method('hydrate')->willReturn($history);
        $this->historyRepository->expects($this->once())->method('save')->with($history);

        $this->creditLimitHistory->logUpdateCreditLimit($credit, 'comment', []);
    }

    /**
     * Test for prepareChangeCurrencyComment method.
     *
     * @return void
     */
    public function testPrepareChangeCurrencyComment()
    {
        $userId = 1;
        $userName = 'John Doe';
        $from = 'USD';
        $to = 'EUR';
        $rate = '0.7500';
        $this->userContext->expects($this->once())
            ->method('getUserId')
            ->willReturn($userId);
        $this->creator->expects($this->once())->method('retrieveCreatorName')
            ->with(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN, $userId)
            ->willReturn($userName);
        $expectedCommentData = [
            'currency_from' => $from,
            'currency_to' => $to,
            'currency_rate' => number_format($rate, 4),
            'user_name' => $userName,
        ];
        $this->assertEquals(
            $expectedCommentData,
            $this->creditLimitHistory->prepareChangeCurrencyComment($from, $to, $rate)
        );
    }

    /**
     * Test `logUpdateItem` with new credit.
     *
     * @return void
     */
    public function testLogUpdateItemWithNewCredit()
    {
        $credit = $this->getMockBuilder(CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originCredit = $this->getMockBuilder(CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originCredit->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->historyRepository->expects($this->never())
            ->method('save');

        $this->creditLimitHistory->logUpdateItem($credit, $originCredit);
    }

    /**
     * Test `logUpdateItem` with changes.
     *
     * @return void
     */
    public function testLogUpdateItemWithChanges()
    {
        $originCreditId = 1;
        $creditLimit = 10;
        $originCurrencyCode = 'EUR';
        $currencyCode = 'USD';
        $currencyRate = 2;
        $userId = 1;
        $userName = 'User name';

        $credit = $this->getMockBuilder(CreditLimit::class)
            ->setMethods(['getCurrencyRate', 'getCurrencyCode', 'getCreditLimit'])
            ->disableOriginalConstructor()
            ->getMock();
        $originCredit = $this->getMockBuilder(CreditLimit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $originCredit->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($originCreditId);

        $this->userContext->expects($this->atLeastOnce())
            ->method('getUserId')
            ->willReturn($userId);
        $this->creator->expects($this->atLeastOnce())
            ->method('retrieveCreatorName')
            ->with(UserContextInterface::USER_TYPE_ADMIN, $userId)
            ->willReturn($userName);

        $originCredit->expects($this->atLeastOnce())
            ->method('getCurrencyCode')
            ->willReturn($originCurrencyCode);
        $credit->expects($this->atLeastOnce())
            ->method('getCurrencyCode')
            ->willReturn($currencyCode);
        $credit->expects($this->atLeastOnce())
            ->method('getCurrencyRate')
            ->willReturn($currencyRate);

        $credit->expects($this->atLeastOnce())
            ->method('getCreditLimit')
            ->willReturn($creditLimit);

        $history = $this->getMockBuilder(HistoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->historyFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($history);
        $this->historyHydrator->expects($this->once())
            ->method('hydrate')
            ->with(
                $history,
                $credit,
                HistoryInterface::TYPE_UPDATED,
                0,
                '',
                null,
                [
                    HistoryInterface::COMMENT_TYPE_UPDATE_CURRENCY => [
                        'currency_from' => $originCurrencyCode,
                        'currency_to' => $currencyCode,
                        'currency_rate' => $currencyRate,
                        'user_name' => $userName
                    ]
                ]
            )
            ->willReturn($history);

        $this->creditLimitHistory->logUpdateItem($credit, $originCredit);
    }
}
