<?php

/**
 * Invitation websites options source
 *
 */
namespace Magento\Invitation\Model\Source\Invitation;

class WebsiteId implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Store
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_store;

    /**
     * @param \Magento\Store\Model\System\Store $store
     */
    public function __construct(\Magento\Store\Model\System\Store $store)
    {
        $this->_store = $store;
    }

    /**
     * Return list of invitation statuses as options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_store->getWebsiteOptionHash();
    }
}
