<?php
namespace Magento\CompanyCredit\Model;

use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Model\ResourceModel\CreditLimit as CreditLimitResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Save handler for the company credit create and update operations.
 */
class SaveHandler
{
    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitFactory
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Model\ResourceModel\CreditLimit
     */
    private $creditLimitResource;

    /**
     * @var \Magento\CompanyCredit\Model\CreditLimitHistory
     */
    private $creditLimitHistory;

    /**
     * @var \Magento\CompanyCredit\Model\CreditCurrencyHistory
     */
    private $creditCurrencyHistory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param CreditLimitFactory $creditLimitFactory
     * @param CreditLimitResource $creditLimitResource
     * @param CreditLimitHistory $creditLimitHistory
     * @param CreditCurrencyHistory $creditCurrencyHistory
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        CreditLimitFactory $creditLimitFactory,
        CreditLimitResource $creditLimitResource,
        CreditLimitHistory $creditLimitHistory,
        CreditCurrencyHistory $creditCurrencyHistory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->creditLimitFactory = $creditLimitFactory;
        $this->creditLimitResource = $creditLimitResource;
        $this->creditLimitHistory = $creditLimitHistory;
        $this->creditCurrencyHistory = $creditCurrencyHistory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Execute save command for the company credit create and update operations.
     *
     * @param CreditLimitInterface $credit
     * @return CreditLimitInterface
     * @throws LocalizedException
     */
    public function execute(CreditLimitInterface $credit)
    {
        $originCredit = $this->getOriginCredit($credit);
        $credit = $this->prepareCreditBeforeSave($credit, $originCredit);

        try {
            $this->creditLimitResource->save($credit);

            // after save history modifications
            if ($originCredit->getId() && ($originCredit->getId() !== $credit->getId())) {
                $this->creditCurrencyHistory->update($originCredit->getId(), $credit->getId());
            }

            $this->logHistoryItems($credit, $originCredit);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Could not save company credit limit'),
                $e
            );
        }

        return $credit;
    }

    /**
     * Get origin credit using id.
     *
     * @param CreditLimitInterface $credit
     * @return CreditLimitInterface
     */
    private function getOriginCredit(CreditLimitInterface $credit)
    {
        $originCredit = $this->creditLimitFactory->create();
        $this->creditLimitResource->load(
            $originCredit,
            $credit->getId()
        );

        return $originCredit;
    }

    /**
     * Prepare credit object before saving.
     *
     * @param CreditLimitInterface $credit
     * @param CreditLimitInterface $originCredit
     * @return CreditLimitInterface
     */
    private function prepareCreditBeforeSave(CreditLimitInterface $credit, CreditLimitInterface $originCredit)
    {
        $credit->setData(array_merge($originCredit->getData(), $credit->getData()));
        $isCurrencyChanged = $credit->getCurrencyCode() != $originCredit->getCurrencyCode();
        if ($isCurrencyChanged && $originCredit->getId()) {
            $credit = $this->convertCreditCurrency($credit, $originCredit);
        }

        if ($originCredit->getCreditLimit() === null && (float)$credit->getCreditLimit() === 0.0) {
            $credit->setCreditLimit(null);
        }

        return $credit;
    }

    /**
     * Log history items using data diff.
     *
     * @param CreditLimitInterface $credit
     * @param CreditLimitInterface $originCredit
     * @return void
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    private function logHistoryItems(CreditLimitInterface $credit, CreditLimitInterface $originCredit)
    {
        $creditLimit = $credit->getCreditLimit();
        $isCreditAllocated = $originCredit->getCreditLimit() === null && $credit->getCreditLimit() > 0;

        if ($isCreditAllocated) {
            $credit->setCreditLimit(null);
        }

        $this->creditLimitHistory->logUpdateItem($credit, $originCredit);

        if ($isCreditAllocated) {
            $credit->setCreditLimit($creditLimit);
            $this->creditLimitHistory->logCredit(
                $credit,
                HistoryInterface::TYPE_ALLOCATED,
                0
            );
        }
    }

    /**
     * Convert credit currency and return new credit object.
     *
     * @param CreditLimitInterface $credit
     * @param CreditLimitInterface $originCredit
     * @return CreditLimitInterface
     */
    private function convertCreditCurrency(CreditLimitInterface $credit, CreditLimitInterface $originCredit)
    {
        $convertedCredit = $this->creditLimitFactory->create();
        $convertedCredit->setData($credit->getData())
            ->setId(null);

        $rate = $credit->getCurrencyRate()
            ?: $this->retrieveCurrencyRateByCurrencyCodes(
                $originCredit->getCurrencyCode(),
                $credit->getCurrencyCode()
            );
        $balance = $this->calculateBalance($credit, $rate);
        $limit = $credit->getCreditLimit() !== null
            ? $credit->getCreditLimit()
            : $originCredit->getCreditLimit() * $rate;

        $convertedCredit->setCreditLimit($limit)
            ->setCurrencyRate($rate)
            ->setBalance($balance);

        return $convertedCredit;
    }

    /**
     * Calculate new credit balance based on currency rate.
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

    /**
     * Get currency conversion rate by currencies codes.
     *
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return float|int
     */
    private function retrieveCurrencyRateByCurrencyCodes($fromCurrencyCode, $toCurrencyCode)
    {
        /** @var \Magento\Directory\Model\Currency $creditCurrency */
        $creditCurrency = $this->priceCurrency->getCurrency(null, $fromCurrencyCode);

        return $creditCurrency->getRate($toCurrencyCode);
    }
}
