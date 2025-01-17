<?php
namespace Magento\Eway\Gateway\Helper\Request;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class Action
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class Action
{
    /**
     * Actions
     */
    const TRANSACTION_ACTION = 'Transaction';

    const ACCESS_CODES_SHARED = 'AccessCodesShared';

    const ACCESS_CODE = 'AccessCode';

    const CANCEL_AUTHORISATION = 'CancelAuthorisation';

    const CAPTURE_PAYMENT = 'CapturePayment';

    /**
     * @var string
     */
    private $action;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param string $action
     * @param ConfigInterface $config
     */
    public function __construct($action, ConfigInterface $config)
    {
        $this->action = $action;
        $this->config = $config;
    }

    /**
     * Get request URL
     *
     * @param string $additionalPath
     * @return string
     */
    public function getUrl($additionalPath = '')
    {
        $gateway = (bool)$this->config->getValue('sandbox_flag')
            ? $this->config->getValue('sandbox_gateway')
            : $this->config->getValue('live_gateway');

        return trim($gateway) . sprintf('/%s%s', $this->action, $additionalPath);
    }
}
