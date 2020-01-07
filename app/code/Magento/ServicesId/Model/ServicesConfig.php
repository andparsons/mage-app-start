<?php
declare(strict_types=1);

namespace Magento\ServicesId\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritdoc
 */
class ServicesConfig implements ServicesConfigInterface
{
    /**
     * Path to the configuration value for the Instance ID
     *
     * @var string
     */
    const CONFIG_PATH_INSTANCE_ID = 'services_connector/services_id/instance_id';

    /**
     * Path to the configuration value for the Environment
     *
     * @var string
     */
    const CONFIG_PATH_ENVIRONMENT = 'services_connector/services_id/environment';

    /**
     * Path to the configuration value for the Environment ID
     *
     * @var string
     */
    const CONFIG_PATH_ENVIRONMENT_ID = 'services_connector/services_id/environment_id';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getInstanceId()
    {
        return $this->config->getValue(self::CONFIG_PATH_INSTANCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getEnvironment()
    {
        return $this->config->getValue(self::CONFIG_PATH_ENVIRONMENT);
    }

    /**
     * @inheritDoc
     */
    public function getEnvironmentId()
    {
        return $this->config->getValue(self::CONFIG_PATH_ENVIRONMENT_ID);
    }
}
