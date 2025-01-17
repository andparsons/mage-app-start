<?php

namespace Magento\Paypal\Helper\Shortcut;

/**
 * Interface \Magento\Paypal\Helper\Shortcut\ValidatorInterface
 *
 */
interface ValidatorInterface
{
    /**
     * Validates shortcut
     *
     * @param string $code
     * @param bool $isInCatalog
     * @return bool
     */
    public function validate($code, $isInCatalog);
}
