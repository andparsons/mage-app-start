<?php

namespace Magento\Backend\Test\Block\Dashboard;

class StoreStats extends \Magento\Backend\Test\Block\Widget\FormTabs
{
    /**
     * Refresh data button
     *
     * @var string
     */
    protected $refreshData = 'button';

    /**
     * Click Refresh Data button
     *
     * return void
     */
    public function refreshData()
    {
        $this->_rootElement->find($this->refreshData)->click();
    }
}
