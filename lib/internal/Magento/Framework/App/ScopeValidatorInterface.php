<?php
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\ScopeValidatorInterface
 *
 */
interface ScopeValidatorInterface
{
    /**
     * Check that scope and scope id is exists
     *
     * @param string $scope
     * @param string $scopeId
     * @return bool
     */
    public function isValidScope($scope, $scopeId = null);
}
