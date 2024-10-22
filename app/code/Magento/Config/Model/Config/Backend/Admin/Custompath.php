<?php

/**
 * Config backend model for "Custom Admin Path" option
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Admin;

/**
 * @api
 * @since 100.0.2
 */
class Custompath extends \Magento\Framework\App\Config\Value
{
    /**
     * Check whether redirect should be set
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getOldValue() != $this->getValue()) {
            $this->_registry->register('custom_admin_path_redirect', true, true);
        }
        return $this;
    }
}
