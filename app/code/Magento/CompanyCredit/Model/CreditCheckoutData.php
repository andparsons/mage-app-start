<?php

namespace Magento\CompanyCredit\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Company\Api\CompanyRepositoryInterface;

/**
 * Prepares checkout data for payment on account method.
 */
class CreditCheckoutData
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param CompanyRepositoryInterface $companyRepository
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     */
    public function __construct(
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        CreditDataProviderInterface $creditDataProvider,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        PriceCurrencyInterface $priceCurrency,
        CompanyRepositoryInterface $companyRepository,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->creditDataProvider = $creditDataProvider;
        $this->quoteRepository = $quoteRepository;
        $this->priceCurrency = $priceCurrency;
        $this->companyRepository = $companyRepository;
        $this->websiteCurrency = $websiteCurrency;
    }

    /**
     * Prepare currency rate string.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $fromCurrencyCode
     * @return float|null
     */
    public function getCurrencyConvertedRate(\Magento\Quote\Api\Data\CartInterface $quote, $fromCurrencyCode)
    {
        $rate = 1;

        if ($fromCurrencyCode !== null && $quote->getQuoteCurrencyCode() != $fromCurrencyCode) {
            $displayCurrencyAmount = (float)$quote->getGrandTotal();
            $creditCurrencyAmount = (float)$this->getGrandTotalInCreditCurrency($quote, $fromCurrencyCode);

            if ($displayCurrencyAmount && $creditCurrencyAmount) {
                $rate = round($creditCurrencyAmount, 2) / round($displayCurrencyAmount, 2);
            }
        }

        return $rate;
    }

    /**
     * Is rate between base and credit currency enabled.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $toCurrencyCode
     * @return bool
     */
    public function isBaseCreditCurrencyRateEnabled(\Magento\Quote\Api\Data\CartInterface $quote, $toCurrencyCode)
    {
        $isEnabled = false;

        if ($quote->getBaseCurrencyCode() == $toCurrencyCode) {
            $isEnabled = true;
        } elseif ($toCurrencyCode !== null) {
            $rate = $this->getBaseRate($quote, $toCurrencyCode);

            if ($rate) {
                $isEnabled = true;
            }
        }

        return $isEnabled;
    }

    /**
     * Get currency rate between base and credit currencies.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $toCurrencyCode
     * @return float
     */
    public function getBaseRate(\Magento\Quote\Api\Data\CartInterface $quote, $toCurrencyCode)
    {
        $toCurrency = $this->websiteCurrency->getCurrencyByCode($toCurrencyCode);
        $fromCurrency = $this->websiteCurrency->getCurrencyByCode($quote->getBaseCurrencyCode());

        return (float)$this->priceCurrency->getCurrency(null, $fromCurrency)->getRate($toCurrency);
    }

    /**
     * Get quote total in credit currency.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $toCurrencyCode
     * @return float|null
     */
    public function getGrandTotalInCreditCurrency(\Magento\Quote\Api\Data\CartInterface $quote, $toCurrencyCode)
    {
        $grandTotal = $quote->getBaseGrandTotal();

        if ($quote->getBaseCurrencyCode() != $toCurrencyCode) {
            try {
                $fromCurrency = $this->websiteCurrency->getCurrencyByCode($quote->getBaseCurrencyCode());
                $toCurrency = $this->websiteCurrency->getCurrencyByCode($toCurrencyCode);
                $grandTotal = $fromCurrency->convert($quote->getBaseGrandTotal(), $toCurrency);
            } catch (\Exception $e) {
                $grandTotal = null;
            }
        }

        return $grandTotal;
    }

    /**
     * Prepare formatted price.
     *
     * @param float|int $price
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency|string $currency
     * @return string
     */
    public function formatPrice($price, $currency)
    {
        return $this->priceCurrency->format($price, false, PriceCurrencyInterface::DEFAULT_PRECISION, null, $currency);
    }

    /**
     * Prepare price format pattern.
     *
     * If currency has currency symbol, currency symbol will be added to price format pattern. If currency doesn't have
     * currency symbol, currency code will be added to price format pattern.
     *
     * @param string $creditCurrencyCode
     * @return string
     */
    public function getPriceFormatPattern($creditCurrencyCode)
    {
        $creditCurrency = $this->websiteCurrency->getCurrencyByCode($creditCurrencyCode);
        $priceFormatPattern = '%s';

        if ($creditCurrency) {
            if ($creditCurrency->getCurrencySymbol()) {
                $priceFormatPattern = $creditCurrency->getCurrencySymbol() . $priceFormatPattern;
            } elseif ($creditCurrency->getCurrencyCode()) {
                $priceFormatPattern = $creditCurrency->getCurrencyCode() . $priceFormatPattern;
            }
        }

        return $priceFormatPattern;
    }

    /**
     * Get current company id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCompanyId()
    {
        if ($this->userContext->getUserId()
            && $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            try {
                $customer = $this->customerRepository->getById($this->userContext->getUserId());

                if ($customer->getExtensionAttributes()
                    && $customer->getExtensionAttributes()->getCompanyAttributes()
                    && $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
                ) {
                    return $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
        }
        return null;
    }
}
