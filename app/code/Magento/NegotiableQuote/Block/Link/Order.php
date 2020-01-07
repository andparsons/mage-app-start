<?php

namespace Magento\NegotiableQuote\Block\Link;

use Magento\Framework\View\Element\Html\Link;

/**
 * Class for link My Order.
 *
 * @api
 * @since 100.0.0
 */
class Order extends Link implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * Get href.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('sales/order/history');
    }

    /**
     * Get label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Orders');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
