<?php
namespace Magento\QuickOrder\Block;

/**
 * Class Sample
 *
 * @api
 * @since 100.0.0
 */
class Sample extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        return '<a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a>';
    }

    /**
     * Get href URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getViewFileUrl($this->getPath());
    }
}
