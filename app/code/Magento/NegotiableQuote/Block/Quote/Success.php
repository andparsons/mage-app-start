<?php
namespace Magento\NegotiableQuote\Block\Quote;

/**
 * Class Success
 *
 * @api
 * @since 100.0.0
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @return int
     */
    public function getQuoteId()
    {
        return (int)$this->getRequest()->getParam('quote_id');
    }

    /**
     * @param int $quoteId
     * @return string
     */
    public function getViewQuoteUrl($quoteId)
    {
        return $this->getUrl('negotiable_quote/quote/view/', ['quote_id' => $quoteId, '_secure' => true]);
    }
}
