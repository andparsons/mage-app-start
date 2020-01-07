<?php

namespace Magento\NegotiableQuote\Helper;

use Magento\Quote\Model\Quote as QuoteModel;

/**
 * Negotiable quote config helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+*/
    const XML_PATH_QUOTE_MINIMUM_AMOUNT = 'quote/general/minimum_amount';
    const XML_PATH_QUOTE_MINIMUM_AMOUNT_MESSAGE = 'quote/general/minimum_amount_message';
    /**#@-*/

    /**
     * Is quote allowed
     * @param QuoteModel $quote
     * @return bool
     */
    public function isQuoteAllowed(QuoteModel $quote)
    {
        return $this->isAllowedAmount($quote->getSubtotalWithDiscount(), $quote->getStoreId());
    }

    /**
     * Is allowed amount
     * @param float $amount
     * @param int|null $store
     * @return bool
     */
    public function isAllowedAmount($amount, $store = null)
    {
        return $amount >= $this->getMinimumAmount($store);
    }

    /**
     * Get minimum quote amount
     * @param null $store
     * @return float
     */
    public function getMinimumAmount($store = null)
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_QUOTE_MINIMUM_AMOUNT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get minimum amount message
     * @return string
     */
    public function getMinimumAmountMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_QUOTE_MINIMUM_AMOUNT_MESSAGE);
    }
}
