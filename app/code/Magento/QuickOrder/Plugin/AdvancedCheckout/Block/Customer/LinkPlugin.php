<?php
namespace Magento\QuickOrder\Plugin\AdvancedCheckout\Block\Customer;

use Magento\AdvancedCheckout\Block\Customer\Link;

/**
 * Plugin to display or hide 'Order by SKU' menu link in customer account.
 */
class LinkPlugin
{
    /**
     * @var \Magento\QuickOrder\Model\Config
     */
    private $config;

    /**
     * @param \Magento\QuickOrder\Model\Config $config
     */
    public function __construct(\Magento\QuickOrder\Model\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Around to html.
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundToHtml(Link $subject, \Closure $proceed)
    {
        return $this->config->isActive() ? '' : $proceed();
    }
}
