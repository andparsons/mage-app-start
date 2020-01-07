<?php
declare(strict_types=1);

namespace Magento\DataExporter\Http\Command;

use GuzzleHttp\Exception\GuzzleException;
use Magento\DataExporter\Exception\UnableSendData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\DataExporter\Http\ConverterInterface;
use Magento\ServicesConnector\Api\ClientResolverInterface;
use Magento\ServicesConnector\Api\KeyNotFoundException;
use Magento\ServicesConnector\Api\KeyValidationInterface;
use Magento\ServicesId\Model\ServicesConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SubmitFeed
 */
class SubmitFeed
{
    /**
     * Config paths
     */
    const ROUTE_CONFIG_PATH = 'magento_saas/routes/';
    const ENVIRONMENT_CONFIG_PATH = 'magento_saas/environment';

    /**
     * Extension name for Services Connector
     */
    const EXTENSION_NAME = 'Magento_DataExporter';

    /**
     * @var ClientResolverInterface
     */
    private $clientResolver;

    /**
     * @var KeyValidationInterface
     */
    private $keyValidator;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ServicesConfigInterface
     */
    private $servicesConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientResolverInterface $clientResolver
     * @param KeyValidationInterface $keyValidator
     * @param ConverterInterface $converter
     * @param ScopeConfigInterface $config
     * @param ServicesConfigInterface $servicesConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientResolverInterface $clientResolver,
        KeyValidationInterface $keyValidator,
        ConverterInterface $converter,
        ScopeConfigInterface $config,
        ServicesConfigInterface $servicesConfig,
        LoggerInterface $logger
    ) {
        $this->clientResolver = $clientResolver;
        $this->keyValidator = $keyValidator;
        $this->converter = $converter;
        $this->config = $config;
        $this->servicesConfig = $servicesConfig;
        $this->logger = $logger;
    }

    /**
     * Build URL to SaaS Service
     *
     * @param string $feedName
     * @return string
     */
    private function getUrl(string $feedName) : string
    {
        $route = '/' . $this->config->getValue(self::ROUTE_CONFIG_PATH . $feedName) . '/';
        $instanceId = $this->servicesConfig->getInstanceId();
        return $route . $instanceId;
    }

    /**
     * Execute call to SaaS Service
     *
     * @param string $feedName
     * @param array $data
     * @return bool
     * @throws UnableSendData
     */
    public function execute(string $feedName, array $data) : bool
    {
        $client = $this->clientResolver->createHttpClient(
            self::EXTENSION_NAME,
            $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
        );
        $headers[] = [$this->converter->getContentTypeHeader()];
        $body = $this->converter->toBody($data);
        $options = [
            'headers' => $headers,
            'body' => $body
        ];

        $result = false;
        try {
            if ($this->validateApiKey()) {
                $response = $client->request(\Zend_Http_Client::POST, $this->getUrl($feedName), $options);
                $result = ($response->getStatusCode() == 200);
            } else {
                $this->logger->error('API Key Validation Failed');
                throw new UnableSendData(__('Unable to send data to service'));
            }
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableSendData(__('Unable to send data to service'));
        } catch (KeyNotFoundException $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableSendData(__('Unable to send data to service'));
        }

        return $result;
    }

    /**
     * Validate the API Gateway Key
     *
     * @return bool
     * @throws KeyNotFoundException
     */
    private function validateApiKey() : bool
    {
        return $this->keyValidator->execute(
            self::EXTENSION_NAME,
            $this->config->getValue(self::ENVIRONMENT_CONFIG_PATH)
        );
    }
}
