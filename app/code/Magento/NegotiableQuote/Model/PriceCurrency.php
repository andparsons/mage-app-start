<?php
namespace Magento\NegotiableQuote\Model;

/**
 * PriceCurrency model for converting and formatting price value on negotiable quote.
 */
class PriceCurrency implements \Magento\Framework\Pricing\PriceCurrencyInterface
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * @inheritdoc
     */
    public function convert($amount, $scope = null, $currency = null)
    {
        $currency = $this->getCurrency($scope, $currency);
        return $this->priceCurrency->convert($amount, $scope, $currency);
    }

    /**
     * @inheritdoc
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        $currency = $this->getCurrency($scope, $currency);
        return $this->priceCurrency->convertAndRound($amount, $scope, $currency, $precision);
    }

    /**
     * @inheritdoc
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $currency = $this->getCurrency($scope, $currency);
        return $this->priceCurrency->format($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * @inheritdoc
     */
    public function convertAndFormat(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        $currency = $this->getCurrency($scope, $currency);
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer, $precision, $scope, $currency);
    }

    /**
     * @inheritdoc
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * @inheritdoc
     */
    public function getCurrency($scope = null, $currency = null)
    {
        if (!empty($currency) && is_string($currency)) {
            return $this->currencyFactory->create()->load($currency);
        }
        
        return $this->priceCurrency->getCurrency($scope, $currency);
    }

    /**
     * @inheritdoc
     */
    public function getCurrencySymbol($scope = null, $currency = null)
    {
        $currency = $this->getCurrency($scope, $currency);
        return $this->priceCurrency->getCurrencySymbol($scope, $currency);
    }
}
