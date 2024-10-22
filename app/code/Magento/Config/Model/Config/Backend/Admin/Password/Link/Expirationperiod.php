<?php

/**
 * Admin Reset Password Link Expiration period backend model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Config\Model\Config\Backend\Admin\Password\Link;

/**
 * @api
 * @since 100.0.2
 */
class Expirationperiod extends \Magento\Framework\App\Config\Value
{
    /**
     * Validate expiration period value before saving
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $expirationPeriod = (int)$this->getValue();

        if ($expirationPeriod < 1) {
            $expirationPeriod = (int)$this->getOldValue();
        }
        $this->setValue((string)$expirationPeriod);
        return $this;
    }
}
