<?php

namespace Magento\NegotiableQuote\Block\Customer\Account\Link;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Link on quote list.
 *
 * @api
 * @since 100.0.0
 */
class Quote extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    protected $quoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface
     */
    protected $customerRestriction;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->quoteHelper = $quoteHelper;
        $this->customerRestriction = $customerRestriction;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->quoteHelper->isEnabled()
        && (
            $this->customerRestriction->isAllowed('Magento_NegotiableQuote::view_quotes')
            || $this->customerRestriction->isAllowed('Magento_NegotiableQuote::all')
        ) ? parent::_toHtml() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
