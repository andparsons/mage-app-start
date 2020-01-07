<?php

namespace Magento\RequisitionList\Block\Link;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Block for requisition list link in customer navigation.
 *
 * @api
 * @since 100.0.0
 */
class RequisitionListLink extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
