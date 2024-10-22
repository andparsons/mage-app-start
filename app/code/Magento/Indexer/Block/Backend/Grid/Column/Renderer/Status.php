<?php
namespace Magento\Indexer\Block\Backend\Grid\Column\Renderer;

class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render indexer status
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = '';
        $text = '';
        switch ($this->_getValue($row)) {
            case \Magento\Framework\Indexer\StateInterface::STATUS_INVALID:
                $class = 'grid-severity-critical';
                $text = __('Reindex required');
                break;
            case \Magento\Framework\Indexer\StateInterface::STATUS_VALID:
                $class = 'grid-severity-notice';
                $text = __('Ready');
                break;
            case \Magento\Framework\Indexer\StateInterface::STATUS_WORKING:
                $class = 'grid-severity-major';
                $text = __('Processing');
                break;
        }
        return '<span class="' . $class . '"><span>' . $text . '</span></span>';
    }
}
