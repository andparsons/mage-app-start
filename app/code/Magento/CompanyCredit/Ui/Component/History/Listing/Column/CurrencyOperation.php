<?php

namespace Magento\CompanyCredit\Ui\Component\History\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\CompanyCredit\Model\WebsiteCurrency;

/**
 * Class prepares data for Amount column of company credit history grid.
 */
class CurrencyOperation extends Column
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
     * CurrencyOperation constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param $websiteCurrency $websiteCurrency
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
                $item[$this->getData('name') . '_original'] = $item[$this->getData('name')];
                if (!in_array($item['type'], [HistoryInterface::TYPE_ALLOCATED, HistoryInterface::TYPE_UPDATED])) {
                    $item[$this->getData('name')] = $this->prepareAmountString($item);
                } else {
                    $item[$this->getData('name')] = '';
                }
            }
        }

        return $dataSource;
    }

    /**
     * Prepare amount string value for the grid.
     *
     * @param array $historyItemData
     * @return string
     */
    private function prepareAmountString(array $historyItemData)
    {
        $currencyCreditCode = isset($historyItemData['currency_credit']) ? $historyItemData['currency_credit'] : null;
        $creditCurrency = $this->websiteCurrency->getCurrencyByCode($currencyCreditCode);
        $amountString = '';
        $amounts = $this->getAmounts($historyItemData);

        if ($amounts) {
            if ($amounts['credit'] == $amounts['operation']) {
                $amountString = $this->prepareAmountValue($amounts['credit'], $creditCurrency);
            } else {
                $amountString = $this->prepareAmountValue($amounts['credit'], $creditCurrency);
                $operationCurrency = $this->websiteCurrency->getCurrencyByCode($historyItemData['currency_operation']);
                $currencyOperationAmountString = $this->prepareAmountValue(
                    $amounts['operation'],
                    $operationCurrency
                );
                $amountString .= ' (' . $currencyOperationAmountString . ')';
                $displayCurrencyRate = $this->getDisplayCurrencyRate($amounts['operation'], $amounts['credit']);
                $amountString .= '<br>' . $historyItemData['currency_credit'] . '/' .
                    $historyItemData['currency_operation'] . ': ' . $displayCurrencyRate;
            }
        }

        return $amountString;
    }

    /**
     * Get amounts data.
     *
     * @param array $historyItemData
     * @return array
     */
    private function getAmounts(array $historyItemData)
    {
        $amounts = [];
        $amount = $historyItemData[$this->getData('name')];
        $rate = $this->getRate($historyItemData);
        $rateCredit = $this->getRateCredit($historyItemData);

        if ($rateCredit) {
            $amounts['credit'] = $amount * $rateCredit;
            $amounts['operation'] = $amount * $rate;
        } else {
            $amounts['operation'] = $amount;

            if ($this->isCurrencyCreditEqualToCurrencyOperation($historyItemData)) {
                $amounts['credit'] = $amounts['operation'];
            } else {
                $amounts['credit'] = $amount / $rate;
            }
        }

        return $amounts;
    }

    /**
     * Get rate.
     *
     * @param array $historyItemData
     * @return float|int
     */
    private function getRate(array $historyItemData)
    {
        return (isset($historyItemData['rate']) && (float)$historyItemData['rate'])
            ? (float)$historyItemData['rate'] : 1;
    }

    /**
     * Get rate between base currency and credit currency.
     *
     * @param array $historyItemData
     * @return float|int
     */
    private function getRateCredit(array $historyItemData)
    {
        return (isset($historyItemData['rate_credit']) && (float)$historyItemData['rate_credit'])
            ? (float)$historyItemData['rate_credit'] : null;
    }

    /**
     * Get currency rate between credit currency and display currency.
     *
     * @param float $amountOperation
     * @param float $amountCredit
     * @return string
     */
    private function getDisplayCurrencyRate($amountOperation, $amountCredit)
    {
        if ($amountOperation && $amountCredit) {
            return number_format(abs($amountOperation / $amountCredit), 4);
        }

        return '';
    }

    /**
     * Is credit currency equal to display currency.
     *
     * @param array $historyItemData
     * @return bool
     */
    private function isCurrencyCreditEqualToCurrencyOperation(array $historyItemData)
    {
        if (!isset($historyItemData['currency_operation']) || !isset($historyItemData['currency_credit'])) {
            return true;
        }

        return $historyItemData['currency_operation'] == $historyItemData['currency_credit'];
    }

    /**
     * Prepare amount value with currency code.
     *
     * @param float $amount
     * @param \Magento\Directory\Model\Currency|string $currency
     * @return string
     */
    private function prepareAmountValue($amount, $currency)
    {
        return $this->priceFormatter->format(
            $amount,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            null,
            $currency
        );
    }
}
