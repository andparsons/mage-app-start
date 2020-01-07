<?php

namespace Magento\NegotiableQuote\Model\Email;

use Magento\Framework\UrlInterface as FrontendUrlBuilder;
use Magento\Backend\Model\UrlInterface as BackendUrlBuilder;

/**
 * Class builds links for emails.
 */
class LinkBuilder
{
    /**
     * @param FrontendUrlBuilder $frontendUrlBuilder
     * @param BackendUrlBuilder $backendUrlBuilder
     */
    public function __construct(
        FrontendUrlBuilder $frontendUrlBuilder,
        BackendUrlBuilder $backendUrlBuilder
    ) {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->backendUrlBuilder = $backendUrlBuilder;
    }

    /**
     * Get backend url.
     *
     * @param string|null $routePath [optional]
     * @param array $routeParams [optional]
     * @return string
     */
    public function getBackendUrl($routePath = null, array $routeParams = [])
    {
        return $this->backendUrlBuilder->getUrl($routePath, $routeParams);
    }

    /**
     * Get frontend url.
     *
     * @param string $routePath
     * @param string $scope
     * @param string $store
     * @param int $quoteId [optional]
     * @return string
     */
    public function getFrontendUrl($routePath, $scope, $store, $quoteId = 0)
    {
        $this->frontendUrlBuilder->setScope($scope);
        $routeParams = ['_current' => false, '_query' => '___store=' . $store];

        if ($quoteId != 0) {
            $routeParams['quote_id'] = $quoteId;
        }

        return $this->frontendUrlBuilder->getUrl($routePath, $routeParams);
    }
}
