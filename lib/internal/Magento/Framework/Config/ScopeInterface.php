<?php
namespace Magento\Framework\Config;

/**
 * Config scope interface.
 *
 * @api
 * @since 100.0.2
 */
interface ScopeInterface
{
    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function getCurrentScope();

    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     */
    public function setCurrentScope($scope);
}
