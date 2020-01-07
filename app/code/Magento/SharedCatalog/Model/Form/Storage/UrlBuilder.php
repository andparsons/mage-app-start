<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Model\Form\Storage;

/**
 * Class UrlBuilder
 */
class UrlBuilder
{
    /**
     * Request param key
     */
    const REQUEST_PARAM_CONFIGURE_KEY = 'configure_key';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get url with configure key from request
     *
     * @param string|null $routePath
     * @param array $routeParams
     * @return string
     */
    public function getUrl($routePath = null, array $routeParams = [])
    {
        $routeParams['_current'] = self::REQUEST_PARAM_CONFIGURE_KEY;
        return $this->urlBuilder->getUrl($routePath, $routeParams);
    }
}
