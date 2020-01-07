<?php

namespace Magento\CompanyCredit\Ui\Component\History\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CompanyCredit\Model\WebsiteCurrency;

/**
 * Class CurrencyCredit.
 */
class CurrencyCredit extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\CompanyCredit\Model\WebsiteCurrency
     */
    private $websiteCurrency;

    /**
     * CurrencyCredit constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param WebsiteCurrency $websiteCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        WebsiteCurrency $websiteCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->websiteCurrency = $websiteCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $currencyCode = isset($item['currency_credit']) ? $item['currency_credit'] : null;
                $currency = $this->websiteCurrency->getCurrencyByCode($currencyCode);
                $item[$this->getData('name')] = $this->priceFormatter->format(
                    $item[$this->getData('name')],
                    false,
                    \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                    null,
                    $currency
                );
            }
        }

        return $dataSource;
    }
}
