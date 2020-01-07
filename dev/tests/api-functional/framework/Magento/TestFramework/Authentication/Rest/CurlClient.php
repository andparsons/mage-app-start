<?php
namespace Magento\TestFramework\Authentication\Rest;

use OAuth\Common\Http\Uri\UriInterface;

/**
 * Custom Client implementation for cURL
 */
class CurlClient extends \OAuth\Common\Http\Client\CurlClient
{
    /**
     * {@inheritdoc}
     */
    public function retrieveResponse(
        UriInterface $endpoint,
        $requestBody,
        array $extraHeaders = [],
        $method = 'POST'
    ) {
        $this->setCurlParameters([CURLOPT_FAILONERROR => true]);
        return parent::retrieveResponse($endpoint, $requestBody, $extraHeaders, $method);
    }
}
