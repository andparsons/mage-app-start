<?php

namespace Magento\QuickOrder\Plugin\CatalogPermissions\Observer;

/**
 * Class CheckQuotePermissionsObserverPlugin
 */
class CheckQuotePermissionsObserverPlugin
{
    /**
     * @var \Magento\QuickOrder\Model\Config
     */
    private $config;

    /**
     * CheckQuotePermissionsObserverPlugin constructor.
     *
     * @param \Magento\QuickOrder\Model\Config $config
     */
    public function __construct(\Magento\QuickOrder\Model\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Here we allow to add hidden products to a cart
     *
     * @param \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver
     */
    public function aroundExecute(
        \Magento\CatalogPermissions\Observer\CheckQuotePermissionsObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        if ($this->config->isActive()) {
            return $subject;
        }
        $result = $proceed($observer);

        return $result;
    }
}
