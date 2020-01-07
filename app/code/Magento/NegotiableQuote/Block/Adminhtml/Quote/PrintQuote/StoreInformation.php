<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\PrintQuote;

use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;

/**
 * Class StoreInformation
 *
 * @api
 * @since 100.0.0
 */
class StoreInformation extends \Magento\Framework\View\Element\Template
{
    /**
     * Get Store Name
     *
     * @param int|null $store
     * @return string
     */
    public function getStoreName($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_NAME,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
