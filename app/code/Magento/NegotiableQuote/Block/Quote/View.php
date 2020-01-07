<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class View.
 *
 * @api
 * @since 100.0.0
 */
class View extends AbstractQuote
{
    /**
     * Get url for quote recalculate action.
     *
     * @return string
     */
    public function getRecalculateUrl()
    {
        return $this->getUrl('negotiable_quote/quote/recalculate');
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getQuote()->getId();
    }

    /**
     * Check status is not ordered or closed.
     *
     * @return bool
     */
    public function isCanRecalculate()
    {
        return $this->getNegotiableQuote()->getStatus() != NegotiableQuoteInterface::STATUS_ORDERED
            && $this->getNegotiableQuote()->getStatus() != NegotiableQuoteInterface::STATUS_CLOSED;
    }
}
