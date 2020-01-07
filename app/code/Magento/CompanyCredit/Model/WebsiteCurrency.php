<?php

namespace Magento\CompanyCredit\Model;

/**
 * Class WebsiteCurrency fetches base currencies of all websites.
 */
class WebsiteCurrency
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array|null
     */
    private $baseCurrencies;

    /**
     * @var array
     */
    private $currencies = [];

    /**
     * WebsiteCurrency constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Is credit currency among websites base currencies.
     *
     * @param string $currencyCode
     * @return bool
     */
    public function isCreditCurrencyEnabled($currencyCode)
    {
        $baseCurrencies = $this->getAllowedCreditCurrencies();

        return isset($baseCurrencies[$currencyCode]);
    }

    /**
     * Get base currencies of all websites.
     *
     * @return array
     */
    public function getAllowedCreditCurrencies()
    {
        if ($this->baseCurrencies !== null) {
            return $this->baseCurrencies;
        }

        $this->baseCurrencies = [];
        foreach ($this->storeManager->getWebsites(true) as $website) {
            $currency = $website->getBaseCurrencyCode();
            $this->baseCurrencies[$currency] = $currency;
        }

        return $this->baseCurrencies;
    }

    /**
     * Get currency by currency code.
     *
     * @param string|null $currencyCode
     * @return \Magento\Directory\Model\Currency
     */
    public function getCurrencyByCode($currencyCode)
    {
        if (isset($this->currencies[$currencyCode])) {
            return $this->currencies[$currencyCode];
        }

        if (!$currencyCode) {
            return $this->storeManager->getStore()->getBaseCurrency();
        }

        $currency = $this->currencyFactory->create();
        $this->currencies[$currencyCode] = $currency->load($currencyCode);

        return $this->currencies[$currencyCode];
    }
}
