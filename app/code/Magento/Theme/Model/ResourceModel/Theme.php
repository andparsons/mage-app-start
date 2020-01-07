<?php
namespace Magento\Theme\Model\ResourceModel;

/**
 * Theme resource model
 */
class Theme extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('theme', 'theme_id');
    }
}
