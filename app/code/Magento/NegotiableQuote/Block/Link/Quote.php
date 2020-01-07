<?php

namespace Magento\NegotiableQuote\Block\Link;

use Magento\Framework\View\Element\Html\Link;

/**
 * Class for link My Quotes.
 *
 * @api
 * @since 100.0.0
 */
class Quote extends Link implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * Get href.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('negotiable_quote/quote');
    }

    /**
     * Get Label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Quotes');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
