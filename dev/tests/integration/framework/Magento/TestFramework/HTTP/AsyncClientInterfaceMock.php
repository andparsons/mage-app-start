<?php
declare(strict_types=1);

namespace Magento\TestFramework\HTTP;

use Magento\Framework\HTTP\AsyncClient\GuzzleAsyncClient;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;

/**
 * Mock for the asynchronous client.
 */
class AsyncClientInterfaceMock implements AsyncClientInterface
{
    /**
     * @var GuzzleAsyncClient
     */
    private $client;

    /**
     * @var Response[]
     */
    private $mockResponses = [];

    /**
     * @var Request|null
     */
    private $lastRequest;

    /**
     * AsyncClientInterfaceMock constructor.
     * @param GuzzleAsyncClient $client
     */
    public function __construct(GuzzleAsyncClient $client)
    {
        $this->client = $client;
    }

    /**
     * Next responses will be as given.
     *
     * @param Response[] $responses
     * @return void
     */
    public function nextResponses(array $responses): void
    {
        $this->mockResponses = $responses;
    }

    /**
     * Last request made.
     *
     * @return Request|null
     */
    public function getLastRequest(): ?Request
    {
        return $this->lastRequest;
    }

    /**
     * @inheritDoc
     */
    public function request(Request $request): HttpResponseDeferredInterface
    {
        $this->lastRequest = $request;
        if ($mockResponse = array_shift($this->mockResponses)) {
            return new MockDeferredResponse($mockResponse);
        }

        return $this->client->request($request);
    }
}
