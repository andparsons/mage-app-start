<?php
namespace Magento\Banner\Model\ResourceModel;

/**
 * Banner Catalogrule Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Catalogrule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize banner catalog rule resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magento_banner_catalogrule', 'rule_id');
    }
}
