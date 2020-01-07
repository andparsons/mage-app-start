<?php
namespace Magento\CompanyCredit\Ui\Component\Form;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CompanyCredit\Api\CreditDataProviderInterface;

/**
 * Prepares configuration for Credit Limit field.
 */
class AmountField extends Field
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currencyFormatter;

    /**
     * @var int
     */
    private $defaultFieldValue = 0;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CreditDataProviderInterface $creditDataProvider
     * @param \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency
     * @param \Magento\Directory\Model\Currency $currencyFormatter
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceCurrency,
        CreditDataProviderInterface $creditDataProvider,
        \Magento\CompanyCredit\Model\WebsiteCurrency $websiteCurrency,
        \Magento\Directory\Model\Currency $currencyFormatter,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->creditDataProvider = $creditDataProvider;
        $this->priceCurrency = $priceCurrency;
        $this->websiteCurrency = $websiteCurrency;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * Prepare component configuration.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        $currency = $this->getCurrency();
        $config['addbefore'] = $this->priceCurrency->getCurrencySymbol(null, $currency);
        $config['value'] = $this->currencyFormatter->formatTxt(
            $this->defaultFieldValue,
            ['display' => \Zend_Currency::NO_SYMBOL]
        );

        $this->setData('config', $config);
    }

    /**
     * Get credit currency for Credit Limit field.
     *
     * @return \Magento\Directory\Model\Currency
     */
    private function getCurrency()
    {
        $currencyCode = null;
        if ($this->getContext()->getRequestParam('id')) {
            $creditLimit = $this->creditDataProvider->get($this->getContext()->getRequestParam('id'));
            $currencyCode = $creditLimit->getCurrencyCode();
        }

        return $this->websiteCurrency->getCurrencyByCode($currencyCode);
    }
}
