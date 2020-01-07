<?php
namespace Magento\Framework\Config;

/**
 * Config validation state interface.
 *
 * @api
 * @since 100.0.2
 */
interface ValidationStateInterface
{
    /**
     * Retrieve current validation state
     *
     * @return boolean
     */
    public function isValidationRequired();
}
