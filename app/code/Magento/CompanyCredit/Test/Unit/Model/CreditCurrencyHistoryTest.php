<?php

namespace Magento\CompanyCredit\Test\Unit\Model;

/**
 * Class CreditCurrencyHistoryTest.
 */
class CreditCurrencyHistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DB\TransactionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionFactory;

    /**
     * @var \Magento\CompanyCredit\Model\CreditCurrencyHistory
     */
    private $creditCurrencyHistory;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->historyRepository = $this->createMock(
            \Magento\CompanyCredit\Model\HistoryRepositoryInterface::class
        );
        $this->searchCriteriaBuilder = $this->createMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->transactionFactory = $this->createPartialMock(
            \Magento\Framework\DB\TransactionFactory::class,
            ['create']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->creditCurrencyHistory = $objectManager->getObject(
            \Magento\CompanyCredit\Model\CreditCurrencyHistory::class,
            [
                'historyRepository' => $this->historyRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'transactionFactory' => $this->transactionFactory,
            ]
        );
    }

    /**
     * Test for update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $currentId = 1;
        $newId = 2;
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilter')
            ->with(\Magento\CompanyCredit\Model\HistoryInterface::COMPANY_CREDIT_ID, $currentId)->willReturnSelf();
        $searchCriteria = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $searchResults = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);
        $this->historyRepository->expects($this->once())
            ->method('getList')->with($searchCriteria)->willReturn($searchResults);
        $historyItem = $this->createMock(\Magento\CompanyCredit\Model\History::class);
        $searchResults->expects($this->once())->method('getItems')->willReturn([$historyItem]);
        $transaction = $this->createMock(\Magento\Framework\DB\Transaction::class);
        $this->transactionFactory->expects($this->once())->method('create')->willReturn($transaction);
        $historyItem->expects($this->once())->method('setCompanyCreditId')->with($newId)->willReturnSelf();
        $transaction->expects($this->once())->method('addObject')->with($historyItem)->willReturnSelf();
        $transaction->expects($this->once())->method('save')->willReturnSelf();
        $this->creditCurrencyHistory->update($currentId, $newId);
    }
}
