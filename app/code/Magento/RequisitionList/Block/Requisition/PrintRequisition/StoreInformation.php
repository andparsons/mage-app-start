<?php
namespace Magento\RequisitionList\Block\Requisition\PrintRequisition;

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
     * Get Store Phone
     *
     * @param int|null $store
     * @return string
     */
    public function getStorePhone($store = null)
    {
        return (string)$this->_scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_PHONE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
