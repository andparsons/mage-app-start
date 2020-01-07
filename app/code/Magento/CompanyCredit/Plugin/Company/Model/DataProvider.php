<?php

namespace Magento\CompanyCredit\Plugin\Company\Model;

use Magento\Company\Model\Company\DataProvider as CompanyDataProvider;
use Magento\CompanyCredit\Api\Data\CreditLimitInterface;
use Magento\CompanyCredit\Api\CreditDataProviderInterface;

/**
 * DataProvider for CompanyCredit form on a company edit page.
 */
class DataProvider
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currencyFormatter;

    /**
     * @param CreditDataProviderInterface $creditDataProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\Currency $currencyFormatter
     */
    public function __construct(
        CreditDataProviderInterface $creditDataProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currencyFormatter
    ) {
        $this->creditDataProvider = $creditDataProvider;
        $this->storeManager = $storeManager;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * After getCompanyResultData.
     *
     * @param CompanyDataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCompanyResultData(CompanyDataProvider $subject, array $result)
    {
        $creditData = [];
        if (!empty($result['id'])) {
            $creditLimit = $this->creditDataProvider->get($result['id']);
            $creditData[CreditLimitInterface::EXCEED_LIMIT] = $creditLimit->getExceedLimit();
            $creditData[CreditLimitInterface::CURRENCY_CODE] = $creditLimit->getCurrencyCode()
                ? $creditLimit->getCurrencyCode()
                : $this->storeManager->getStore()->getBaseCurrency()->getCurrencyCode();
            $creditData[CreditLimitInterface::CREDIT_LIMIT] = $this->currencyFormatter->formatTxt(
                $creditLimit->getCreditLimit(),
                ['display' => \Zend_Currency::NO_SYMBOL]
            );
        } else {
            $creditData[CreditLimitInterface::CURRENCY_CODE] = $this->storeManager->getStore()
                ->getBaseCurrency()->getCurrencyCode();
        }
        $result['company_credit'] = $creditData;
        return $result;
    }
}
