<?php
namespace Magento\Eway\Gateway\Http;

use Magento\Eway\Gateway\Helper\Request\Action;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

/**
 * Class AbstractTransferFactory
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
abstract class AbstractTransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * @var Action
     */
    protected $action;

    /**
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param Action $action
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        Action $action
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->action = $action;
    }

    /**
     * Returns API key config value.
     *
     * @return string
     */
    protected function getApiKey()
    {
        return (bool) $this->config->getValue('sandbox_flag')
            ? $this->config->getValue('sandbox_api_key')
            : $this->config->getValue('live_api_key');
    }

    /**
     * Returns API password config value.
     *
     * @return string
     */
    protected function getApiPassword()
    {
        return (bool) $this->config->getValue('sandbox_flag')
            ? $this->config->getValue('sandbox_api_password')
            : $this->config->getValue('live_api_password');
    }
}
