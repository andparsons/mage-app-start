<?php
namespace Magento\Customer\Model;

/**
 * @api
 * @since 100.0.2
 */
class Registration
{
    /**
     * Check whether customers registration is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        return true;
    }
}
