<?php
namespace Magento\Solr\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Psr\Log\LoggerInterface;

class ConnectionManager
{
    /**
     * @var \Magento\Solr\Model\Client\Solarium
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface
     */
    private $clientHelper;

    /**
     * @param ClientFactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        ClientOptionsInterface $clientHelper,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->clientHelper = $clientHelper;
    }

    /**
     * Get shared connection
     *
     * @throws \RuntimeException
     * @return \Magento\Solr\Model\Client\Solarium
     */
    public function getConnection()
    {
        if (!$this->client) {
            $this->connect();
        }

        return $this->client;
    }

    /**
     * Connect to Solarium client with default options
     *
     * @throws \RuntimeException
     * @return void
     */
    private function connect()
    {
        try {
            $this->client = $this->clientFactory->create($this->clientHelper->prepareClientOptions());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \RuntimeException('Solr client is not set.');
        }
    }
}
