<?php
namespace Magento\NegotiableQuote\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PriceAdmin.
 */
class PriceAdmin extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->priceFormatter = $priceFormatter;
        $this->storeManager = $storeManager;
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
                $baseCurrency = $this->retrieveBaseCurrency($item);
                $quoteCurrency = $this->retrieveQuoteCurrencyAvailable($item);
                $price = $this->priceFormatter->format(
                    $item['base_' . $this->getData('name')],
                    false,
                    2,
                    null,
                    $baseCurrency
                );
                if (isset($baseCurrency)
                    && isset($quoteCurrency)
                    && $baseCurrency != $quoteCurrency
                    && $convertedPrice = $this->retrieveQuotePrice($item, $baseCurrency, $quoteCurrency)
                ) {
                    $convertedPrice = $this->priceFormatter->format(
                        $convertedPrice,
                        false,
                        2,
                        null,
                        $quoteCurrency
                    );
                    $price = $price . ' (' . $convertedPrice . ')';
                }
                $item[$this->getData('name')] = $price;
            }
        }

        return $dataSource;
    }

    /**
     * Return currency rate for item.
     *
     * @param array $item
     * @param string $baseCurrency
     * @param string $quoteCurrency
     * @return float
     */
    private function retrieveQuotePrice(array $item, $baseCurrency, $quoteCurrency)
    {
        $currency = $this->priceFormatter->getCurrency(null, $baseCurrency);
        if (isset($item['base_currency_code'])
            && isset($item['quote_currency_code'])
            && $baseCurrency == $item['base_currency_code']
            && $quoteCurrency == $item['quote_currency_code']
            && (!$currency->getRate($quoteCurrency) || $currency->getRate($quoteCurrency) == $item['rate'])
        ) {
            return $item[$this->getData('name')];
        }

        return $currency->getRate($quoteCurrency)
            ? $currency->convert($item['base_' . $this->getData('name')], $quoteCurrency)
            : 0;
    }

    /**
     * Retrieve base currency for item from store.
     *
     * @param array $item
     * @return string
     */
    private function retrieveBaseCurrency(array $item)
    {
        $blockedStatuses = [NegotiableQuoteInterface::STATUS_CLOSED, NegotiableQuoteInterface::STATUS_ORDERED];
        $status = isset($item['status_original']) ? $item['status_original'] : $item['status'];
        $currency = isset($item['base_currency_code']) ? $item['base_currency_code'] : null;
        if (!in_array($status, $blockedStatuses) || empty($currency)) {
            $currency = $this->storeManager->getStore($item['store_id'])->getBaseCurrency()->getCode();
        }
        return $currency;
    }

    /**
     * Retrieve quote currency for item from store.
     *
     * @param array $item
     * @return string
     */
    private function retrieveQuoteCurrencyAvailable(array $item)
    {
        $blockedStatuses = [NegotiableQuoteInterface::STATUS_CLOSED, NegotiableQuoteInterface::STATUS_ORDERED];
        $status = isset($item['status_original']) ? $item['status_original'] : $item['status'];
        $currency = isset($item['quote_currency_code']) ? $item['quote_currency_code'] : null;
        if (!in_array($status, $blockedStatuses) || empty($currency)) {
            $store = $this->storeManager->getStore($item['store_id']);
            $allowedCurrency = $store->getAvailableCurrencyCodes(true);
            $currency = in_array($currency, $allowedCurrency) && $store->getBaseCurrency()->getRate($currency)
                ? $currency
                : $store->getCurrentCurrency()->getCode();
        }
        return $currency;
    }
}
