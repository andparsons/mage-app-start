<?php
namespace Magento\QuickOrder\Block;

/**
 * Class Link
 *
 * @api
 * @since 100.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /** @var \Magento\AdvancedCheckout\Helper\Data  */
    protected $_customerHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\AdvancedCheckout\Helper\Data $customerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\AdvancedCheckout\Helper\Data $customerHelper,
        array $data = []
    ) {
        $this->_customerHelper = $customerHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('quickorder');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Quick Order');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_customerHelper->isSkuApplied()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
