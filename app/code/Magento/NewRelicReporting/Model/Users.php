<?php

namespace Magento\NewRelicReporting\Model;

class Users extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize users model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Users::class);
    }
}
