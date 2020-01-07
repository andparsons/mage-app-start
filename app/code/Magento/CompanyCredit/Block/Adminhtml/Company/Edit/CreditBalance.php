<?php

namespace Magento\CompanyCredit\Block\Adminhtml\Company\Edit;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CreditBalance.
 *
 * @api
 * @since 100.0.0
 */
class CreditBalance extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'company/edit/credit_balance.phtml';

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface
     */
    private $creditLimit;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditDataInterface
     */
    private $credit;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * CreditBalance constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit,
        \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        array $data = []
    ) {
        $this->creditLimit = $creditLimit;
        $this->creditDataProvider = $creditDataProvider;
        $this->priceFormatter = $priceFormatter;
        $this->websiteCurrency = $websiteCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Get outstanding balance.
     *
     * @return float|null
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
     * Check whether balance less than 0.
     *
     * @return bool
     */
    public function isOutstandingBalanceNegative()
    {
        $creditBalance = $this->getCredit() ? $this->getCredit()->getBalance() : 0;

        return $creditBalance < 0;
    }

    /**
     * Get credit limit.
     *
     * @return float|null
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
     * Get available credit.
     *
     * @return float|null
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
     * Get credit object.
     *
     * @return \Magento\CompanyCredit\Api\Data\CreditDataInterface|null
     */
    private function getCredit()
    {
        $companyId = $this->getRequest()->getParam('id');
        if ($companyId && empty($this->credit)) {
            try {
                $this->credit = $this->creditDataProvider->get($companyId);
            } catch (NoSuchEntityException $e) {
                $this->credit = null;
            }
        }

        return $this->credit;
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
