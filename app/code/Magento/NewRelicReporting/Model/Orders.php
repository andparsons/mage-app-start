<?php

namespace Magento\NewRelicReporting\Model;

class Orders extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize orders model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Orders::class);
    }
}
