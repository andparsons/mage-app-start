<?php

namespace Magento\CompanyCredit\Controller\Adminhtml\Currency;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * This class returns currency rate or an error, if rate is not available.
 */
class GetRate extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * GetRate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
    }

    /**
     * Get currency rate.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $currencyFrom = $this->getRequest()->getParam('currency_from');
        $currencyTo = $this->getRequest()->getParam('currency_to');
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            /**
             * @var \Magento\Directory\Model\Currency $currency
             * @var \Magento\Directory\Model\Currency $targetCurrency
             */
            $currency = $this->priceCurrency->getCurrency();
            $targetCurrency = $this->priceCurrency->getCurrency(null, $currencyTo);
            $currencyRate = $currency->getCurrencyRates($currencyFrom, [$currencyTo]);
            $currencySymbol = $targetCurrency->getCurrencySymbol();
            if (!empty($currencyRate[$currencyTo])) {
                $result->setData(
                    [
                        'status' => 'success',
                        'currency_rate' => number_format($currencyRate[$currencyTo], 4),
                        'currency_symbol' => $currencySymbol
                    ]
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['status' => 'error', 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['status' => 'error', 'error' => __('Something went wrong. Please try again later.')]);
            $this->logger->critical($e);
        }

        return $result;
    }
}
