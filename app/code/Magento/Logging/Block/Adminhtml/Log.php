<?php

/**
 * Log grid container
 */
namespace Magento\Logging\Block\Adminhtml;

class Log extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Header text getter
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Admin Actions Log');
    }

    /**
     * Grid contents getter
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml();
    }
}
