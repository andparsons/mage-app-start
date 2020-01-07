<?php

namespace Magento\CompanyCredit\Block\Company;

/**
 * Class CreditBalance.
 *
 * @api
 * @since 100.0.0
 */
class CreditBalance extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider
     */
    private $customerProvider;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditDataInterface
     */
    private $credit;

    /**
     * CreditBalance constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider $customerProvider
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider $customerProvider,
        \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerProvider = $customerProvider;
        $this->creditDataProvider = $creditDataProvider;
        $this->priceFormatter = $priceFormatter;
        $this->websiteCurrency = $websiteCurrency;
    }

    /**
     * Get credit.
     *
     * @return \Magento\CompanyCredit\Api\Data\CreditDataInterface
     */
    public function getCredit()
    {
        if ($this->credit === null && $this->customerProvider->getCurrentUserCredit()) {
            $companyId = $this->customerProvider->getCurrentUserCredit()->getCompanyId();
            $this->credit = $this->creditDataProvider->get($companyId);
        }

        return $this->credit;
    }

    /**
     * Is outstanding balance negative.
     *
     * @return bool
     */
    public function isOutstandingBalanceNegative()
    {
        $creditBalance = $this->getCredit() ? $this->getCredit()->getBalance() : 0;

        return $creditBalance < 0;
    }

    /**
     * Get outstanding balance.
     *
     * @return float
     */
    public function getOutstandingBalance()
    {
        $creditBalance = $this->getCredit() ? $this->getCredit()->getBalance() : 0;

        return $this->priceFormatter->format(
            $creditBalance,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getCreditCurrency()
        );
    }

    /**
     * Get available credit.
     *
     * @return float
     */
    public function getAvailableCredit()
    {
        $creditAvailableLimit = $this->getCredit() ? $this->getCredit()->getAvailableLimit() : 0;

        return $this->priceFormatter->format(
            $creditAvailableLimit,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getCreditCurrency()
        );
    }

    /**
     * Get credit limit.
     *
     * @return float
     */
    public function getCreditLimit()
    {
        $creditCreditLimit = $this->getCredit() ? $this->getCredit()->getCreditLimit() : 0;

        return $this->priceFormatter->format(
            $creditCreditLimit,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $this->getCreditCurrency()
        );
    }

    /**
     * Get credit currency.
     *
     * @return \Magento\Directory\Model\Currency
     */
    private function getCreditCurrency()
    {
        $creditCurrencyCode = null;
        if ($this->getCredit()) {
            $creditCurrencyCode = $this->getCredit()->getCurrencyCode();
        }

        return $this->websiteCurrency->getCurrencyByCode($creditCurrencyCode);
    }
}
