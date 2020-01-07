<?php

namespace Magento\Reports\Test\Block\Adminhtml\Viewed;

use Magento\Backend\Test\Block\GridPageActions;

/**
 * Class Action
 * Action block for Report Filter
 */
class Action extends GridPageActions
{
    /**
     * Show Report button
     *
     * @var string
     */
    protected $showReportButton = '#filter_form_submit';

    /**
     * Show report button click
     *
     * @return void
     */
    public function showReport()
    {
        $this->_rootElement->find($this->showReportButton)->click();
    }
}
