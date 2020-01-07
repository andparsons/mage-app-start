<?php
namespace Magento\NegotiableQuote\Block\Order\Info;

/**
 * Quote block
 *
 * @api
 * @since 100.0.0
 */
class Quote extends \Magento\NegotiableQuote\Block\Adminhtml\Order\Info\Quote
{
    /**
     * Retrieve negotiable quote name
     *
     * @return string|null
     */
    public function getTitleLink()
    {
        return $this->getQuoteName();
    }

    /**
     * Get url for quote
     *
     * @return string
     */
    public function getViewQuoteUrl()
    {
        return $this->getUrl('negotiable_quote/quote/view/', [
            'quote_id' => $this->getQuote()->getId(),
            '_scope' => $this->getQuoteStore()->getCode(),
            '_scope_to_url' => true,
            '_nosid' => true
        ]);
    }

    /**
     * Is quote store enabled
     *
     * @return bool
     */
    public function isQuoteStoreEnabled()
    {
        return $this->getQuoteStore()->isActive();
    }

    /**
     * Is quote placed from current store
     *
     * @return bool
     */
    public function isCurrentStoreQuote()
    {
        return $this->getQuoteStore()->getId() == $this->_storeManager->getStore()->getId();
    }

    /**
     * Get quote store
     *
     * @return \Magento\Store\Api\Data\StoreInterface|\Magento\Store\Model\Store
     */
    private function getQuoteStore()
    {
        return $this->_storeManager->getStore($this->getQuote()->getStoreId());
    }
}
