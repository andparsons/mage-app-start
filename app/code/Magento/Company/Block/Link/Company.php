<?php

namespace Magento\Company\Block\Link;

use Magento\Framework\View\Element\Html\Link;

/**
 * Class Company.
 *
 * @api
 * @since 100.0.0
 */
class Company extends Link implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * Get href
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('company');
    }

    /**
     * Get Label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Company Structure');
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
