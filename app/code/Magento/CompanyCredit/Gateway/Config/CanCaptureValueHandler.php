<?php
namespace Magento\CompanyCredit\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Model\Order;

/**
 * Config can capture, depending on order status.
 */
class CanCaptureValueHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value.
     *
     * @param array $subject
     * @param int|null $storeId [optional]
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->configInterface->getValue('order_status', $storeId) == Order::STATE_PROCESSING;
    }
}
