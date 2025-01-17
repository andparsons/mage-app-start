<?php

namespace Magento\NewRelicReporting\Model;

class System extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize system updates model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\System::class);
    }
}
