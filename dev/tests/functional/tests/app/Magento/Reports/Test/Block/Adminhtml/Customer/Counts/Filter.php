<?php

namespace Magento\Reports\Test\Block\Adminhtml\Customer\Counts;

use Magento\Reports\Test\Block\Adminhtml\AbstractFilter;

/**
 * Class Filter
 * Filter for Order count Report
 */
class Filter extends AbstractFilter
{
    /**
     * Date fields
     *
     * @var array
     */
    protected $dateFields = ['report_from', 'report_to'];
}
