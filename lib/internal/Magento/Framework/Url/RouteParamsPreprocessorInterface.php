<?php
namespace Magento\Framework\Url;

/**
 * Route parameters preprocessor interface.
 */
interface RouteParamsPreprocessorInterface
{
    /**
     * Processes route params.
     *
     * @param string $areaCode
     * @param string|null $routePath
     * @param array|null $routeParams
     * @return array|null
     */
    public function execute($areaCode, $routePath, $routeParams);
}
