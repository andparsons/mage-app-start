<?php

namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

/**
 * Class creates new companyCredit object, updates history log, removes old companyCredit object.
 */
class CreditCurrency
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\CreditCurrencyHistory
     */
    private $creditCurrencyHistory;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitHistory
     */
    private $creditLimitHistory;

    /**
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param CreditCurrencyHistory $creditCurrencyHistory
     * @param WebsiteCurrency $websiteCurrency
     * @param CreditLimitHistory $creditLimitHistory
     */
    public function __construct(
        \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        CreditCurrencyHistory $creditCurrencyHistory,
        WebsiteCurrency $websiteCurrency,
        CreditLimitHistory $creditLimitHistory
    ) {
        $this->creditLimitFactory = $creditLimitFactory;
        $this->creditLimitRepository = $creditLimitRepository;
        $this->creditCurrencyHistory = $creditCurrencyHistory;
        $this->websiteCurrency = $websiteCurrency;
        $this->creditLimitHistory = $creditLimitHistory;
    }

    /**
     * Update company credit data.
     *
     * @param CreditLimitInterface $currentCreditLimit
     * @param array $companyCreditData
     * @param float $currencyRate
     * @return CreditLimitInterface
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws \Exception
     */
    public function change(
        CreditLimitInterface $currentCreditLimit,
        array $companyCreditData,
        $currencyRate
    ) {
        if (!$this->websiteCurrency->isCreditCurrencyEnabled($companyCreditData[CreditLimitInterface::CURRENCY_CODE])) {
            throw new LocalizedException(
                __('The selected currency is not available. Please select a different currency.')
            );
        }

        /**
         * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
         */
        $creditLimit = $this->creditLimitFactory->create();
        $companyCreditData[CreditLimitInterface::COMPANY_ID] = $currentCreditLimit->getCompanyId();
        $companyCreditData[CreditLimitInterface::BALANCE] = $this->calculateBalance(
            $currentCreditLimit,
            $currencyRate
        );
        $companyCreditData[CreditLimitInterface::CREDIT_LIMIT] =
            $companyCreditData[CreditLimitInterface::CREDIT_LIMIT] ?: null;
        $creditLimit->setData($companyCreditData);
        $this->creditLimitRepository->save($creditLimit);
        $this->creditCurrencyHistory->update($currentCreditLimit->getId(), $creditLimit->getId());
        $this->creditLimitRepository->delete($currentCreditLimit);
        $comment = $this->creditLimitHistory->prepareChangeCurrencyComment(
            $currentCreditLimit->getCurrencyCode(),
            $companyCreditData[CreditLimitInterface::CURRENCY_CODE],
            $currencyRate
        );
        $this->creditLimitHistory->logUpdateCreditLimit(
            $creditLimit,
            '',
            [
                HistoryInterface::COMMENT_TYPE_UPDATE_CURRENCY => $comment
            ]
        );
        return $creditLimit;
    }

    /**
     * Calculate new credit limit based on currency rate.
     *
     * @param CreditLimitInterface $creditLimit
     * @param float $currencyRate
     * @return float
     */
    private function calculateBalance(CreditLimitInterface $creditLimit, $currencyRate)
    {
        $currentBalance = $creditLimit->getBalance();

        return $currentBalance * $currencyRate;
    }
}
