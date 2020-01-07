<?php
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
