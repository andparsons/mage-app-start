<?php

namespace Magento\RequisitionList\Block\Link;

use Magento\Framework\View\Element\Html\Link;

/**
 * Block for requisition list link in customer navigation.
 *
 * @api
 * @since 100.0.0
 */
class Lists extends Link implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * Get href.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('requisition_list/requisition/index');
    }

    /**
     * Get Label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Requisition Lists');
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
