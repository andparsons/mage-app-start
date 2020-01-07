<?php

/**
 * Newsletter subscribe block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Block;

/**
 * @api
 * @since 100.0.2
 */
class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}
