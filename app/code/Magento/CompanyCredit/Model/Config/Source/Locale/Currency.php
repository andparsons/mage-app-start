<?php

namespace Magento\CompanyCredit\Model\Config\Source\Locale;

/**
 * Class Currency is a source model, that provides list of allowed currencies for all websites.
 */
class Currency implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\Framework\Locale\Bundle\CurrencyBundle
     */
    private $currencyBundle;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * Currency constructor.
     *
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        \Magento\Framework\Locale\Bundle\CurrencyBundle $currencyBundle,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->websiteCurrency = $websiteCurrency;
        $this->currencyBundle = $currencyBundle;
        $this->localeResolver = $localeResolver;
    }

    /**
     * To option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->options === null) {
            $options = $this->currencyBundle->get($this->localeResolver->getLocale())['Currencies'];
        }

        $this->options = [];
        $allowedCurrencies = $this->websiteCurrency->getAllowedCreditCurrencies();

        foreach ($options as $code => $option) {
            if (!isset($allowedCurrencies[$code])) {
                continue;
            }

            $creditCurrency = $option[1];
            $this->options[] = ['label' => $creditCurrency, 'value' => $code];
        }

        return $this->options;
    }
}
