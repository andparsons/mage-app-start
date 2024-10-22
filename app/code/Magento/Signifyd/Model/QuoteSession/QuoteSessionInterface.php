<?php
namespace Magento\Signifyd\Model\QuoteSession;

/**
 * Interface QuoteSessionInterface
 */
interface QuoteSessionInterface
{
    /**
     * Returns quote from session.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote();
}
