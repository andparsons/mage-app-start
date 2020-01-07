<?php
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkboxes;

class Extended extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * Prepare data for renderer
     *
     * @return array
     */
    public function _getValues()
    {
        return $this->getColumn()->getValues();
    }
}
