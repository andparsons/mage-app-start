<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Configure;

use Magento\Directory\Model\Currency;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Custom price column component
 */
class CustomPrice extends AbstractColumn
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $urlBuilder, $components, $data);
        $this->currency = $priceCurrency->getCurrency();
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->currency->format($item[$fieldName], ['display' => ''], false);
                }
            }
        }

        return $dataSource;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->prepareCurrencySymbol();
        parent::prepare();
    }

    /**
     * Prepares currency symbol
     *
     * @return $this
     */
    protected function prepareCurrencySymbol()
    {
        if (isset($this->_data['config'])) {
            $this->_data['config']['currencySymbol'] = $this->currency->getCurrencySymbol();
        }

        return $this;
    }
}
