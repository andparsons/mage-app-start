<?php
namespace Magento\Eway\Gateway\Http\Client;

use Zend_Http_Response;

/**
 * Class ResponseFactory
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class ResponseFactory
{
    /**
     * Create a new Zend_Http_Response object from a string
     *
     * @param string $response
     * @return Zend_Http_Response
     */
    public function create($response)
    {
        return Zend_Http_Response::fromString($response);
    }
}
