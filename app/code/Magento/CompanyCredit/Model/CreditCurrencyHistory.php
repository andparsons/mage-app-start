<?php

namespace Magento\CompanyCredit\Model;

/**
 * Class performs history log updates during credit currency changes.
 */
class CreditCurrencyHistory
{
    /**
     * @var \Magento\CompanyCredit\Model\HistoryRepositoryInterface
     */
    private $historyRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;

    /**
     * CreditCurrencyHistory constructor.
     *
     * @param \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     */
    public function __construct(
        \Magento\CompanyCredit\Model\HistoryRepositoryInterface $historyRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->historyRepository = $historyRepository;
        $this->transactionFactory = $transactionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Update company credit ids in company credit history.
     *
     * @param int $currentCompanyCreditId
     * @param int $companyCreditId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function update($currentCompanyCreditId, $companyCreditId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(\Magento\CompanyCredit\Model\HistoryInterface::COMPANY_CREDIT_ID, $currentCompanyCreditId)
            ->create();
        $historyItems = $this->historyRepository->getList($searchCriteria)->getItems();

        if ($historyItems) {
            $transaction = $this->transactionFactory->create();

            /**
             * @var \Magento\CompanyCredit\Model\HistoryInterface $historyItem
             */
            foreach ($historyItems as $historyItem) {
                $historyItem->setCompanyCreditId($companyCreditId);
                $transaction->addObject($historyItem);
            }

            $transaction->save();
        }
    }
}
