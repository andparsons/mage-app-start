<?php
namespace Magento\Framework\Config;

/**
 * Config scope list interface.
 *
 * @api
 * @since 100.0.2
 */
interface ScopeListInterface
{
    /**
     * Retrieve list of all scopes
     *
     * @return string[]
     */
    public function getAllScopes();
}
