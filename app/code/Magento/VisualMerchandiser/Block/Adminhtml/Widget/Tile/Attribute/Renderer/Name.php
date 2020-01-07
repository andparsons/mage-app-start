<?php

namespace Magento\VisualMerchandiser\Block\Adminhtml\Widget\Tile\Attribute\Renderer;

class Name extends \Magento\VisualMerchandiser\Block\Adminhtml\Widget\Tile\Attribute\Renderer
{
    /**
     * @return string
     */
    public function render()
    {
        return '<span>' . $this->escaper->escapeHtml($this->getValue()) . '</span></br>';
    }
}
