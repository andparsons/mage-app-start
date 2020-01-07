<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View;

/**
 * Adminhtml sales order create items block
 *
 * @api
 * @since 100.0.0
 */
class Items extends \Magento\NegotiableQuote\Block\Quote\AbstractQuote
{
    /**
     * Define block ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('negotiable_quote_quote_view');
    }

    /**
     * Accordion header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Items Quoted');
    }

    /**
     * Returns all visible items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        if ($this->getQuote(true)) {
            $items = $this->getQuote(true)->getAllVisibleItems();
        }

        return $items;
    }
}
