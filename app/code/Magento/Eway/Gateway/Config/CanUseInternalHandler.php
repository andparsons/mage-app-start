<?php
namespace Magento\Eway\Gateway\Config;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Eway\Model\Adminhtml\Source\ConnectionType;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Class CanUseInternalHandler
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 eWay payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class CanUseInternalHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     * @return int|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        switch ($this->config->getValue('connection_type', $storeId)) {
            case ConnectionType::CONNECTION_TYPE_DIRECT:
                return 1;
            default:
                return 0;
        }
    }
}
