<?php
namespace Magento\Eway\Block;

use Magento\Payment\Block\Form;
use Magento\Payment\Model\Config;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Eway\Model\Ui\Direct\ConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Eway\Model\Adminhtml\Source\ConnectionType;

/**
 * Class Payment
 *
 * @api
 * @since 100.0.2
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class Payment extends Template
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Gets payment config.
     *
     * @return string
     */
    public function getPaymentConfig()
    {
        return json_encode(
            [
                'code' => ConfigProvider::EWAY_CODE,
                'cryptUrl' => $this->config->getValue('crypt_script'),
                'encryptKey' => (bool)$this->config->getValue('sandbox_flag')
                    ? $this->config->getValue('sandbox_encryption_key')
                    : $this->config->getValue('live_encryption_key'),
                'endpoint' => (bool)$this->config->getValue('sandbox_flag')
                    ? ConfigProvider::ENDPOINT_SANDBOX
                    : ConfigProvider::ENDPOINT_PRODUCTION
            ],
            JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Gets connection type.
     *
     * @return string
     */
    public function getConnectionType()
    {
        return ConnectionType::CONNECTION_TYPE_DIRECT;
    }

    /**
     * Gets payment code.
     *
     * @return string
     */
    public function getCode()
    {
        return ConfigProvider::EWAY_CODE;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if ($this->config->getValue('connection_type') !== ConnectionType::CONNECTION_TYPE_DIRECT) {
            return '';
        }

        return parent::toHtml();
    }
}
