<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\NegotiableQuote\Block\System\Config\Form\Field\DefaultExpirationPeriod;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class Expiration provides expiration date for quote.
 */
class Expiration
{
    const DATE_QUOTE_NEVER_EXPIRES = '0000-00-00';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * Expiration constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ResolverInterface $resolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
        $this->resolver = $resolver;
    }

    /**
     * Get default expiration date.
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return \DateTime|null
     */
    public function getExpirationPeriodTime($quote)
    {
        $date = null;
        $this->quote = $quote;
        $customerExpirationPeriod = $this->getCustomerExpirationPeriod();
        if (empty($customerExpirationPeriod)) {
            return $date;
        }
        if (!$customerExpirationPeriod->getData('expirationPeriod')
            && ($customerExpirationPeriod->getData('status') !== NegotiableQuoteInterface::STATUS_EXPIRED)
        ) {
            $date = $this->retrieveDefaultExpirationDate();
        } elseif ($customerExpirationPeriod->getData('expirationPeriod')
            && $customerExpirationPeriod->getData('expirationPeriod') !== self::DATE_QUOTE_NEVER_EXPIRES) {
            $configTimezone = $this->localeDate->getConfigTimezone();
            $date = new \DateTime(
                $customerExpirationPeriod->getData('expirationPeriod'),
                new \DateTimeZone($configTimezone)
            );
        }
        return $date;
    }

    /**
     * Get expiration date from quote.
     *
     * @return \Magento\Framework\DataObject|null
     */
    protected function getCustomerExpirationPeriod()
    {
        $customerExpirationPeriod = null;
        if ($this->quote) {
            $quoteExtensionAttributes = $this->quote->getExtensionAttributes();
            if ($quoteExtensionAttributes
                && $quoteExtensionAttributes->getNegotiableQuote()
            ) {
                $customerExpirationPeriod = new \Magento\Framework\DataObject(
                    [
                        'expirationPeriod' => $quoteExtensionAttributes->getNegotiableQuote()->getExpirationPeriod(),
                        'status' => $quoteExtensionAttributes->getNegotiableQuote()->getStatus(),
                    ]
                );
            }
        }

        return $customerExpirationPeriod;
    }

    /**
     * Retrieve expiration date when default.
     *
     * @return \DateTime|null
     */
    public function retrieveDefaultExpirationDate()
    {
        $date = null;
        $expirationPeriodCount = $this->scopeConfig->getValue(
            DefaultExpirationPeriod::DEFAULT_EXPIRATION_PERIOD_COUNT,
            ScopeInterface::SCOPE_WEBSITE
        );
        $expirationPeriodTime = $this->scopeConfig->getValue(
            DefaultExpirationPeriod::DEFAULT_EXPIRATION_PERIOD_TIME,
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($expirationPeriodCount && $expirationPeriodTime) {
            if (array_key_exists($expirationPeriodTime, DefaultExpirationPeriod::$defaultExpirationPeriod)) {
                $date = $this->localeDate->date();
                $date->modify('+' . (int)$expirationPeriodCount . ' ' . $expirationPeriodTime);
            }
        }

        return $date;
    }
}
