<?php

/**
 * Frontend form key content block
 */
namespace Magento\Cookie\Block\Html;

/**
 * @api
 * @since 100.0.2
 */
class Notices extends \Magento\Framework\View\Element\Template
{
    /**
     * Get Link to cookie restriction privacy policy page
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getPrivacyPolicyLink()
    {
        return $this->_urlBuilder->getUrl('privacy-policy-cookie-restriction-mode');
    }
}
