<?php

namespace Magento\Backend\Block\Widget\Button;

/**
 * Interface \Magento\Backend\Block\Widget\Button\ToolbarInterface
 *
 */
interface ToolbarInterface
{
    /**
     * Push buttons into toolbar
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return void
     * @api
     */
    public function pushButtons(
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    );
}
